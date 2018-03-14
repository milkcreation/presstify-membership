<?php
/*
 * @Overridable
 */

namespace tiFy\Plugins\Membership;

use tiFy\Core\Control\Control;
use tiFy\Plugins\Membership\Actions;
use tiFy\Plugins\Membership\Membership;

class Template extends \tiFy\App\Factory
{
    /**
     * Variable de requête
     * @var string
     */
    protected static $QueryVar = 'tify_membership';

    /**
     * Template d'affichage courant
     * @var null
     */
    protected static $Template = null;

    /**
     * Notification
     * @var null
     */
    protected static $Notice = null;

    /**
     * Classe de rappel de l'interface d'authentification
     * @var \tiFy\Plugins\Membership\Login
     */
    private static $Login;

    /**
     * CONSTRUCTEUR
     *
     * return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des dépendances
        self::$Login = $this->appGetContainer('tiFy\Plugins\Membership\Login');

        // Définition des événement de déclenchement
        $this->tFyAppAddAction('wp');
        $this->tFyAppAddAction('wp_enqueue_scripts');
        $this->tFyAppAddFilter('body_class');
        $this->tFyAppAddFilter('the_content');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * A l'issue du chargement complet
     *
     * @return void
     */
    final public function wp()
    {
        if ( ! $template = static::getTemplate()) :
            return;
        endif;

        // Lancement de actions
        $callback = join('', array_map('ucfirst', preg_split('/_/', $template)));
        if (is_callable('tiFy\Plugins\Membership\Actions::' . $callback)) :
            call_user_func('tiFy\Plugins\Membership\Actions::' . $callback);
        endif;
    }

    /**
     * Mise en file des scripts de l'interface utilisateur
     */
    final public function wp_enqueue_scripts()
    {
        // Bypass
        if ( ! self::isTemplate()) :
            return;
        endif;

        Control::enqueue_scripts('Notices');
    }

    /**
     * Définition des classes de la balise body
     *
     * @return array
     */
    final public function body_class($classes)
    {
        if ($template = static::getTemplate()) :
            $classes[] = "tiFyPluginMembershipBody";
            $classes[] = "tiFyPluginMembershipBody-" . $template;
        endif;

        return $classes;
    }

    /**
     * Filtrage de contenu d'un post
     *
     * @return string
     */
    final public function the_content($content)
    {
        // Bypass
        if ( ! in_the_loop()) :
            return $content;
        endif;
        if ( ! is_singular()) :
            return $content;
        endif;
        if (Options::HookID() !== get_the_ID()) :
            return $content;
        endif;

        $contentConfig = Membership::tFyAppConfig('content', '');

        $output = "";
        if ($contentConfig === 'before') :
            $output .= $content;
        endif;

        $output .= self::content(static::getTemplate());

        if ($contentConfig === 'after') :
            $output .= $content;
        endif;

        return $output;
    }

    /**
     * CONTROLEURS
     */
    /** == Vérifie si la page courante affiche un gabarit du plugin == **/
    final public static function isTemplate($template = null)
    {
        if (is_null($template)) :
            return ! empty(static::getTemplate());
        else :
            return ($template === static::getTemplate());
        endif;
    }

    /** == Récupération du template courant == **/
    final public static function getTemplate()
    {
        if (static::$Template) :
            return static::$Template;
        elseif (isset($_REQUEST[static::$QueryVar])) :
            return static::$Template = esc_attr($_REQUEST[static::$QueryVar]);
        elseif (is_singular() && (Options::HookID() === get_the_ID())) :
            return 'home';
        endif;
    }

    /** == Récupération de l'url d'un template == **/
    final public static function getTemplateUrl($template)
    {
        return esc_url(add_query_arg([static::$QueryVar => $template], Membership::getBaseUri()));
    }

    /** == Définition d'une notification == **/
    final public static function setNotice($message = '', $type = 'info')
    {
        return self::$Notice = [
            'message' => $message,
            'type'    => $type
        ];
    }

    /* = LIENS = */
    /** == Liens vers les pages de template == **/
    final public static function url($template)
    {
        return is_callable('static::url_' . $template) ? call_user_func('static::url_' . $template) : static::url_default($template);
    }

    /** == Lien par défaut == **/
    public static function url_default($template)
    {
        return self::getTemplateUrl($template);
    }

    /* = NOTIFICATIONS = */
    /** == Notification d'une page de template == **/
    final public static function notice($template)
    {
        return is_callable('static::notice_' . $template) ? call_user_func('static::notice_' . $template) : static::notice_default($template);
    }

    /** == Notification par défaut == **/
    public static function notice_default($template)
    {
        return tify_control_notices(['text' => static::$Notice['message'], 'type' => static::$Notice['type']], false);
    }

    /** == Message de notification de l'interface de modification de profil == **/
    public static function notice_user_account()
    {
        if ( ! is_user_logged_in()) {
            return;
        }
        if (User::isActive()) {
            return;
        }

        $text = "";
        $text .= __('Votre compte est en attente d\'activation, vous ne pourrez pas accèder aux ressources réservées aux membres pour l\'instant. Merci de votre compréhension.',
            'tify');
        $text .= "\t\t<br/><a href=\"" . static::url('activation_email') . "\" title=\"" . __('Envoyer un mail de confirmation.',
                'tify') . "\">" . __('Renvoyer un mail de confirmation.', 'tify') . "</a>";

        return tify_control_notices(['text' => $text, 'type' => 'info'], false);
    }

    /** == Message de notification d'envoi de mail d'activation du compte == **/
    public static function notice_activation_email()
    {
        return tify_control_notices([
            'text' => __('Un email d\'activation va vous être envoyé, cliquer sur le lien pour activer votre compte.',
                'tify'),
            'type' => 'info'
        ], false);
    }

    /** == Message de confirmation d'activation du compte == **/
    public static function notice_activate()
    {
        $output = "";
        $output .= tify_control_notices([
            'text' => __('Félicitations, votre compte est désormais actif.', 'tify'),
            'type' => 'success'
        ], false);
        if ( ! is_user_logged_in()) :
            $output .= tify_control_notices([
                'text' => __('Avant de poursuivre veuillez vous connecter.', 'tify'),
                'type' => 'info'
            ], false);
        endif;

        return $output;
    }

    /* = CONTENU DES PAGES = */
    /** == Contenu d'une page de template == **/
    final static public function content($template)
    {
        return is_callable('static::content_' . $template) ? call_user_func('static::content_' . $template) : static::content_default($template);
    }

    /** == Contenu par défaut == **/
    public static function content_default($template)
    {
        $output = "";
        $output .= "<div class=\"tiFyPluginMembership\">\n";
        // Entête
        $output .= "\t<header class=\"tiFyPluginMembership-Header\">" . static::header($template) . "</header>\n";
        // Corps de page
        $output .= "\t<section class=\"tiFyPluginMembership-Body\">" . static::title($template) . (static::$Notice ? static::notice($template) : '') . static::body($template) . "</section>\n";
        // Pied de page
        $output .= "\t<footer class=\"tiFyPluginMembership-Footer\">" . static::footer($template) . "</footer>\n";
        $output .= "</div>\n";

        return $output;
    }

    /* = ENTÊTE = */
    /** == Entête d'une page de template == **/
    final public static function header($template)
    {
        return is_callable('static::header_' . $template) ? call_user_func('static::header_' . $template) : static::header_default($template);
    }

    /** == Entête par défaut == **/
    public static function header_default($template)
    {
        return '';
    }

    /* = TITRE DES PAGES = */
    /** == Titre d'une page de template == **/
    final public static function title($template)
    {
        return is_callable('static::title_' . $template) ? call_user_func('static::title_' . $template) : static::title_default($template);
    }

    /** == Titre par défaut == **/
    public static function title_default($template)
    {
        switch ($template) :
            default :
            case '404' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Page introuvable', 'tify') . "</h3>";
                break;
            case 'home' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Espace Membres', 'tify') . "</h3>";
                break;
            case 'user_account' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Modifier mes paramètres',
                        'tify') . "</h3>";
                break;
            case 'user_list' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Liste des membres', 'tify') . "</h3>";
                break;
            case 'login_form' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Authentification', 'tify') . "</h3>";
                break;
            case 'subscribe_form' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Inscription', 'tify') . "</h3>";
                break;
            case 'activate' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Activation', 'tify') . "</h3>";
                break;
            case 'activation_email' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Envoi de l\'email d\'activation',
                        'tify') . "</h3>";
                break;
            case 'unsubscribe' :
                $title = "<h3 class=\"tiFyPluginMembership-BodyTitle\">" . __('Désinscription', 'tify') . "</h3>";
                break;
        endswitch;

        return $title;
    }

    /* = CORPS DE PAGES = */
    /** == Corps de page d'un template == **/
    final public static function body($template)
    {
        return is_callable('static::body_' . $template) ? call_user_func('static::body_' . $template) : static::body_default($template);
    }

    /** == Corps de page par défaut == **/
    public static function body_default($template)
    {
        return static::body_404();
    }

    /** == Corps de page introuvable == **/
    public static function body_404()
    {
        return "<p>" . __('Désolé, cette page est malheureusement introuvable.', 'tify') . "</p>";
    }

    /** == Corps de page d'accueil == **/
    public static function body_home()
    {
        $output = "";
        if ( ! is_user_logged_in()) :
            $output .= static::body_login_form();
            $output .= static::body_subscribe_form();
        else :
            $output .= "<div class=\"tiFyPluginMembership-logout\">\n";
            $output .= static::logout_link();
            $output .= "</div>\n";
            $output .= static::body_user_account();
        endif;

        return $output;
    }

    /** == Corps de page du formulaire d'authentification == **/
    public static function body_login_form()
    {
        $output = "";
        $output .= "<div class=\"tiFyPluginMembership-LoginInterface\">\n";
        $output .= "\t<div class=\"tiFyPluginMembership-loginForm\">\n";
        $output .= static::login_form();
        $output .= "\t</div>\n";
        $output .= "\t<div class=\"tiFyPluginMembership-loginLostPassword\">\n";
        $output .= static::lostpassword_link();
        $output .= "\t</div>\n";
        $output .= "</div>\n";

        return $output;
    }

    /** == Corps de page du formulaire d'inscription == **/
    public static function body_subscribe_form()
    {
        return "<div class=\"tiFyPluginMembership-subscribe_form tiFyPluginMembership-form\">" . static::subscribe_form() . "</div>";
    }

    /** == Corps de page du formulaire de modification du compte utilisateur == **/
    public static function body_user_account()
    {
        $output = "";
        if ( ! is_user_logged_in()) :
            $output .= static::body_login_form();
        else :
            $output .= "<div class=\"tiFyPluginMembership-user_account_form tiFyPluginMembership-form\">" . static::user_account_form() . "</div>";
        endif;

        return $output;
    }

    /** == Corps de page du formulaire de modification du compte utilisateur == **/
    public static function body_user_list()
    {
        $output = "";
        $output .= "<div class=\"tiFyPluginMembership-user_list tiFyPluginMembership-list\">" . static::user_list() . "</div>";

        return $output;
    }

    /** == Corps de page de l'activation du compte == **/
    public static function body_activate()
    {
        $output = "";
        if ( ! is_user_logged_in()) :
            $output .= static::body_login_form();
        endif;

        return $output;
    }

    /** == Corps de page de la désinscription du compte == **/
    public static function body_unsubscribe()
    {
        return "<p>" . __('Votre compte a été supprimé.', 'tify') . "</p>";
    }

    /** == Corps de page de la liste des membres == **/
    public static function body_list_user()
    {
        return "";
    }

    /* = PIED DE PAGE = */
    /** == Pied de page d'un template == **/
    final public static function footer($template)
    {
        return is_callable('static::footer_' . $template) ? call_user_func('static::footer_' . $template) : static::footer_default($template);
    }

    /** == Pied de page par défaut == **/
    public static function footer_default()
    {
        return '';
    }

    /* = ELEMENTS DE TEMPLATE = */
    /** == Affichage du bouton d'accès à l'interface d'authentification == **/
    public static function login_form_button($args = [])
    {
        $defaults = [
            'text' => __('S\'authentifier', 'tify')
        ];
        $args     = wp_parse_args($args, $defaults);

        $output = "";
        $output .= "<a href=\"" . static::url('login_form') . "\" title=\"" . __('Authentification à l\'espace membres.',
                'tify') . "\">" . $args['text'] . "</a>";

        return $output;
    }

    /**
     * Affichage du formulaire d'authentification
     *
     * @return string
     */
    public static function login_form($args = [])
    {
        return self::$Login->login_form($args);
    }

    /**
     * Affichage des erreurs de formulaire
     *
     * @return string
     */
    public static function login_errors()
    {
        return self::$Login->formErrors();
    }

    /**
     * Affichage du bouton de récupération de mot de passe oublié
     *
     * @return string
     */
    public static function lostpassword_link($args = [])
    {
        return self::$Login->lostpassword_link($args);
    }

    /**
     * Url de déconnection
     *
     * @return string
     */
    public static function logout_url()
    {
        return self::$Login->get_logout_url();
    }

    /**
     * Affichage du bouton de déconnection
     *
     * @return string
     */
    public static function logout_link($args = [])
    {
        return self::$Login->logout_link($args);
    }

    /** == Affichage du bouton d'accès au formulaire d'inscription == **/
    public static function subscribe_form_button($args = [])
    {
        $defaults = [
            'text' => __('S\'inscrire', 'tify')
        ];
        $args     = wp_parse_args($args, $defaults);

        $output = "";
        $output .= "<a href=\"" . static::url('subscribe_form') . "\" title=\"" . __('Inscription à l\'espace membres.',
                'tify') . "\" class=\"tify_forum-subscribe_button\">" . $args['text'] . "</a>";

        return $output;
    }

    /** == Affichage du formulaire d'inscription == **/
    public static function subscribe_form()
    {
        return tify_form_display('tiFyPluginMembershipSubscribeForm', false);
    }

    /** == Affichage du formulaire de modification de données personnel d'un contributeur == **/
    public static function user_account_form()
    {
        return tify_form_display('tiFyPluginMembershipAccountForm', false);
    }

    /** == Affichage de la liste de gestion des utilisateurs == **/
    public static function user_list()
    {
        return '';
    }
}