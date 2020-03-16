<?php

namespace Statamic\Addons\FormWithAttachments;

use Statamic\Extend\Listener;
use Statamic\Contracts\Forms\Submission;
use Statamic\API\Email;
use Statamic\API\File;

class FormWithAttachmentsListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'Form.submission.creating' => 'handleSubmission'
    ];

    public function handleSubmission(Submission $submission)
    {
        $assets = [];
        $emails = [];
        $file_deletion = false;

        $formset = $submission->formset()->name();
        $settings_forms = collect(array_get($this->getConfig(), 'forms', []));
        $settings_formsets = $settings_forms->pluck('formset')->unique()->filter();

        if (! $settings_formsets->contains($formset)) {
            return false;
        }

        foreach ($submission->fields() as $key => $value) {
            if (isset($value['type']) && $value['type'] === 'asset' && $submission->get($key)) {
                array_push($assets, root_path(trim($submission->get($key), '/')));
            }
        }

        foreach ($settings_forms as $form) {
            foreach ($form['settings'] as $setting) {
                $single_email = [];
                $single_email['subject'] = $setting['subject'];
                $single_email['recipient'] = $setting['recipient'];
                $single_email['template'] = $setting['template'];
                array_push($emails, $single_email);
            }
            $file_deletion = (Boolean)$form['file_deletion'];
        }

        $submissionData = $submission->toArray();

        foreach ($emails as $email) {
            $email_builder = Email::create();
            if (empty($assets)) {
                $email_builder->to($email['recipient'])->subject($email['subject'])->template($email['template'])->with($submissionData);
            } else {
                $email_builder->to($email['recipient'])->subject($email['subject'])->template($email['template'])->with($submissionData);
                foreach ($assets as $asset) {
                    $email_builder->attach($asset);
                }
            }
            $email_builder->send();
        }

        if ($file_deletion && !empty($assets)) {
            foreach ($assets as $asset) {
                File::delete($asset);
            }
        }
    }
}
