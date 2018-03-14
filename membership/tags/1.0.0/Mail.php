<?php
namespace tiFy\Plugins\Membership;

use \tiFy\Lib\Mailer\Mailer;

class Mail
{
    public function activation($user_id)
    {
        if (!$u = get_userdata($user_id)) :
            return;
        endif;
        if (!$mailer = Membership::tFyAppConfig('mailer')) :
            return;
        endif;
        if (empty($mailer['activation'])) :
            return;
        endif;

        $args = (is_array($mailer['activation'])) ? $mailer['activation'] : [];

        $args = wp_parse_args($args, [
                'subject'        => sprintf(__('Activer votre compte sur le site %s', 'tify'), get_bloginfo('name')),
                'to'             => $u->user_email,
                'html'           => file_get_contents(Membership::tFyAppDirname() . '/Mailer/activation.html'),
                'html_body_wrap' => false,
                'reset_css'      => false,
                'text'           => file_get_contents(Membership::tFyAppDirname() . '/Mailer/activation.txt'),
                'css'            => [
                    Membership::tFyAppDirname() . '/Mailer/activation.css'
                ],
                'merge_vars'     => [
                    'SITE:NAME'        => get_bloginfo('name'),
                    'SITE:DESCRIPTION' => get_bloginfo('description'),
                    'FNAME'            => $u->first_name,
                    'LNAME'            => $u->last_name,
                    'LINK:ACTIVATION'  => esc_url(
                        add_query_arg(
                            [
                                'tify_membership' => 'activate',
                                'token'           => User::getActivationKey($user_id)
                            ],
                            Membership::getBaseUri()
                        )
                    ),
                    'LINK:UNSUB'       => esc_url(
                        add_query_arg(
                            [
                                'tify_membership' => 'unsubscribe',
                                'email'           => $u->user_email,
                                'token'           => User::getUnsubscribeToken($user_id)
                            ],
                            Membership::getBaseUri()
                        )
                    )
                ]
            ]);

        new Mailer($args);
    }
}