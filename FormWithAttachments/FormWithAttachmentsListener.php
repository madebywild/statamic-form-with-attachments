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

        foreach ($submission->fields() as $key => $value) {
            if (isset($value['type']) && $value['type'] === 'asset' && $submission->get($key)) {
                array_push($assets, $submission->get($key));
            }
        }

        $settings = $this->getConfig();

        foreach ($settings['forms'] as $form) {
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
