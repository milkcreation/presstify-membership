<?php

use tiFy\Plugins\Membership\Membership;

/**
 * Vérifie si la page courante affiche un template de membership
 *
 * @param null $template
 *
 * @return mixed
 */
function is_tify_membership($template = null)
{
    $Template = Membership::tFyAppContainer()->get('tiFy\Plugins\Membership\Template');

    return $Template::isTemplate($template);
}

/**
 * Affichage d'un template de membership
 *
 * @param null $template
 * @param bool $echo
 *
 * @return mixed
 */
function tify_membership_content($template = null, $echo = true)
{
    $Template = Membership::tFyAppContainer()->get('tiFy\Plugins\Membership\Template');

    $output = $Template::content($template);

    if ($echo) :
        echo $output;
    else :
        return $output;
    endif;
}