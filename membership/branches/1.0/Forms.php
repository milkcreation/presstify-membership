<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Membership;

class Forms extends \tiFy\App\Factory
{
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();

        // Déclencheurs
        add_action('tify_form_register', [$this, 'tify_form_register']);
        add_action('tify_form_register_addon', [$this, 'tify_form_register_addon']);
    }

    /* = DECLENCHEURS = */
    /** == Déclaration des formulaires == **/
    final public function tify_form_register()
    {
        $forms = Membership::tFyAppConfig('forms');

        // Formulaire d'inscription
        $subscribe_form = $this->subscribeForm();
        if (isset($forms['subscribe'])) :
            $subscribe_form = wp_parse_args($forms['subscribe'], $subscribe_form);
        endif;

        $subscribe_form['addons']['tiFyPluginMembership'] = [
            'roles' => Membership::getRoles()
        ];
        tify_form_register('tiFyPluginMembershipSubscribeForm', $subscribe_form);

        // Formulaire de modification des informations de compte
        if (is_user_logged_in() && ($current_user = wp_get_current_user())) :
            $account_form = $this->accountForm($current_user);

            if (isset($forms['account'])) :
                $account_form = wp_parse_args($forms['account'], $account_form);
            endif;

            $account_form['addons']['tiFyPluginMembership'] = [
                'roles' => Membership::getRoles()
            ];
            tify_form_register('tiFyPluginMembershipAccountForm', $account_form);
        endif;
    }

    /** == Déclaration de l'addon == **/
    public function tify_form_register_addon()
    {
        tify_form_register_addon('tiFyPluginMembership', '\tiFy\Plugins\Membership\Forms\Addons\Members');
    }

    /* = CONTROLEURS = */
    /** == Définition du formulaire d'inscription == **/
    public function subscribeForm()
    {
        return [
            'title'   => __('Formulaire d\'inscription à l\'espace membres.', 'tify'),
            'fields'  => [
                [
                    'slug'        => 'login',
                    'label'       => __('Identifiant', 'tify'),
                    'placeholder' => __('Identifiant (obligatoire)', 'tify'),
                    'type'        => 'input',
                    'required'    => true,
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'user_login']
                    ]
                ],
                [
                    'slug'         => 'email',
                    'label'        => __('E-mail', 'tify'),
                    'placeholder'  => __('E-mail (obligatoire)', 'tify'),
                    'type'         => 'input',
                    'required'     => true,
                    'integrity_cb' => 'is_email',
                    'addons'      => [
                        'tiFyPluginMembership' => ['userdata' => 'user_email']
                    ]
                ],
                [
                    'slug'        => 'firstname',
                    'label'       => __('Prénom', 'tify'),
                    'placeholder' => __('Prénom', 'tify'),
                    'type'        => 'input',
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'first_name']
                    ]
                ],
                [
                    'slug'        => 'lastname',
                    'label'       => __('Nom', 'tify'),
                    'placeholder' => __('Nom', 'tify'),
                    'type'        => 'input',
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'last_name']
                    ]
                ],
                [
                    'slug'         => 'password',
                    'label'        => __('Mot de passe', 'tify'),
                    'placeholder'  => __('Mot de passe (obligatoire)', 'tify'),
                    'type'         => 'password',
                    'autocomplete' => 'off',
                    'required'     => true,
                    'addons'      => [
                        'tiFyPluginMembership' => ['userdata' => 'user_pass']
                    ]
                ],
                [
                    'slug'         => 'confirm',
                    'label'        => __('Confirmation de mot de passe', 'tify'),
                    'placeholder'  => __('Confirmation de mot de passe (obligatoire)', 'tify'),
                    'type'         => 'password',
                    'autocomplete' => 'off',
                    'onpaste'      => false,
                    'required'     => true,
                    'integrity_cb' => [
                        'function' => 'compare',
                        'args'     => ['%%password%%'],
                        'error'    => __('Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre',
                            'tify')
                    ]
                ],
                [
                    'slug'        => 'captcha',
                    'label'       => __('Code de sécurité', 'tify'),
                    'placeholder' => __('Code', 'tify'),
                    'type'        => 'simple-captcha-image',
                    'required'    => true
                ]
            ],
            'buttons' => [
                'submit' => __('S\'inscrire', 'tify'),
            ],
            'notices' => [
                'success' => __('Votre demande d\'inscription a bien été enregistrée', 'tify')
            ],
            'options' => [

            ]
        ];
    }

    /** == Définition du formulaire de modification de compte == **/
    public function accountForm($current_user)
    {
        return [
            'title'   => __('Formulaire de modification de compte personnel des membres', 'tify'),
            'fields'  => [
                [
                    'slug'        => 'login',
                    'label'       => __('Votre identifiant', 'tify'),
                    'placeholder' => __('Votre identifiant', 'tify'),
                    'type'        => 'input',
                    'value'       => $current_user->user_login,
                    'required'    => true,
                    'readonly'    => true,
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'user_login']
                    ]
                ],
                [
                    'slug'         => 'email',
                    'label'        => __('Votre  E-mail', 'tify'),
                    'placeholder'  => __('Votre  E-mail', 'tify'),
                    'type'         => 'input',
                    'value'        => $current_user->user_email,
                    'required'     => true,
                    'integrity_cb' => 'is_email',
                    'addons'      => [
                        'tiFyPluginMembership' => ['userdata' => 'user_email']
                    ]
                ],
                [
                    'slug'        => 'firstname',
                    'label'       => __('Votre Prénom', 'tify'),
                    'placeholder' => __('Votre Prénom', 'tify'),
                    'value'       => $current_user->user_firstname,
                    'type'        => 'input',
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'first_name']
                    ]
                ],
                [
                    'slug'        => 'lastname',
                    'label'       => __('Nom', 'tify'),
                    'placeholder' => __('Nom', 'tify'),
                    'value'       => $current_user->user_lastname,
                    'type'        => 'input',
                    'addons'     => [
                        'tiFyPluginMembership' => ['userdata' => 'last_name']
                    ]
                ],
                [
                    'slug'         => 'password',
                    'label'        => __('Nouveau mot de passe', 'tify'),
                    'placeholder'  => __('Nouveau mot de passe', 'tify'),
                    'type'         => 'password',
                    'autocomplete' => 'off',
                    'addons'      => [
                        'tiFyPluginMembership' => ['userdata' => 'user_pass']
                    ]
                ],
                [
                    'slug'         => 'confirm',
                    'label'        => __('Confirmation de nouveau mot de passe', 'tify'),
                    'placeholder'  => __('Confirmation de nouveau mot de passe', 'tify'),
                    'type'         => 'password',
                    'autocomplete' => 'off',
                    'integrity_cb' => [
                        'function' => 'compare',
                        'args'     => ['%%password%%'],
                        'error'    => __('Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre',
                            'tify')
                    ]
                ]
            ],
            'buttons' => [
                'submit' => __('Mettre à jour', 'tify'),
            ],
            'notices' => [
                'success' => __('Vos informations personnelles ont été mises à jour', 'tify')
            ],
            'options' => [
                'success_cb' => 'form'
            ]
        ];
    }
}