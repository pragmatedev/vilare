<?php

// phpcs:disable

namespace FM\Blocks;

use FM\Blocks\Block;

class Contact extends Block
{
    public function __construct()
    {
        $this->setId('contact');
        $this->setTitle('Contact');
        $this->setSchema(
            [
                'title' => 'required|string|max:60',
            ]
        );
        $this->setData(
            [
                'title' => 'Contact',
            ]
        );
    }

    /**
     * @action admin_post_contact
     * @action admin_post_nopriv_contact
     */
    public function handle(): void
    {
        $config = [
            'to' => 'hello@footmate.pro',
            'subject' => __('New Contact Message', 'fm'),
            'message' => join(
                "\r\n",
                [
                    "Name: {$_POST['name']}",
                    "Email: {$_POST['email']}",
                    "Phone: {$_POST['phone']}",
                    "Contact By: {$_POST['type']}",
                ]
            ),
        ];

        $result = wp_mail($config['to'], $config['subject'], $config['message']);

        if (empty($result)) {
            wp_die(__('Form submission failed.', 'fm'));
        } else {
            wp_die(__('Form submitted successfully.', 'fm'));
        }
    }
}
