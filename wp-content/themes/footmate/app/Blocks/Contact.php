<?php

// phpcs:disable

namespace FM\Blocks;

use FM\Blocks\Block;
use FM\Core\Validation;

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
        $data = Validation::validate(
            $_POST,
            [
                'name' => 'required|string|min:2|max:100',
                'email' => 'required|email',
                'type' => 'required|string|in:email,phone',
                'privacy' => 'required|accepted',
                'phone' => 'required_if:type,phone|string|regex:/^[\d+\- ]+$/i',
                'nonce' => 'required|nonce',
            ]
        );

        if (is_wp_error($data)) {
            wp_die(esc_attr($data->get_error_message()));
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
            'headers' => ['Content-Type: text/html; charset=UTF-8'],
        ];

        $data = wp_mail($config['to'], $config['subject'], $config['message'], $config['headers']);

        if (empty($data)) {
            wp_die(__('Form submission failed.', 'fm'));
        } else {
            wp_die(__('Form submitted successfully.', 'fm'));
        }
    }

    public function store(string $email): bool
    {
        if (empty($email)) {
            wp_die(__('Email is required.', 'fm'));
        }

        if (! is_email($email)) {
            wp_die(__('Invalid email address.', 'fm'));
        }

        $post = wp_insert_post(
            [
                'post_type' => 'contact',
                'meta_input' => [
                    'email' => $email,
                ],
            ]
        );

        return ! empty($post);
    }
}
