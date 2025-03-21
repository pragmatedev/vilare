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
        if (empty($_POST['name'])) {
            wp_die(__('Name is required.', 'fm'));
        }

        if (empty($_POST['email'])) {
            wp_die(__('Email is required.', 'fm'));
        }

        if (empty($_POST['type'])) {
            wp_die(__('Type is required.', 'fm'));
        }

        if (empty($_POST['privacy'])) {
            wp_die(__('You must accept privacy policy.', 'fm'));
        }

        if ($_POST['type'] === 'phone' && empty($_POST['phone'])) {
            wp_die(__('Phone is required.', 'fm'));
        }

        if (strlen($_POST['name']) < 2) {
            wp_die(__('Name is too short.', 'fm'));
        }

        if (strlen($_POST['name']) > 100) {
            wp_die(__('Name is too long.', 'fm'));
        }

        if (! is_email($_POST['email'])) {
            wp_die(__('Invalid email address.', 'fm'));
        }

        if (! empty($_POST['phone']) && ! preg_match("/^[\d+\- ]+$/", $_POST['phone'])) {
            wp_die(__('Invalid phone number.', 'fm'));
        }

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
