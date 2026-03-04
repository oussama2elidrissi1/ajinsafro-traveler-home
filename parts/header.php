<?php
/**
 * Part: Custom header (topbar + navbar)
 * Header settings managed from Laravel admin /admin/settings/home-page
 * Design based on AjinSafro modern travel theme
 *
 * @package AjinsafroTravelerHome
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$hdr = ajth_get_header_settings();

if ( empty( $hdr['enabled'] ) ) {
    return;
}

$socials = isset( $hdr['socials'] ) && is_array( $hdr['socials'] ) ? $hdr['socials'] : array();

$social_icons = array(
    'facebook'  => '<i class="fab fa-facebook-f"></i>',
    'twitter'   => '<i class="fab fa-twitter"></i>',
    'youtube'   => '<i class="fab fa-youtube"></i>',
    'instagram' => '<i class="fab fa-instagram"></i>',
    'linkedin'  => '<i class="fab fa-linkedin-in"></i>',
);

$menu_icons = array(
    'packages'    => '<i class="fas fa-suitcase-rolling"></i>',
    'hebergement' => '<i class="fas fa-hotel"></i>',
    'activites'   => '<i class="fas fa-camera"></i>',
    'transfert'   => '<i class="fas fa-car-side"></i>',
    'hajj'        => '<i class="fas fa-kaaba"></i>',
    'guide'       => '<i class="fas fa-map-signs"></i>',
);
?>

<header class="aj-header" id="aj-header">

    <?php if ( ! empty( $hdr['topbar_enabled'] ) ) : ?>
    <!-- Top Bar -->
    <div class="aj-topbar">
        <div class="aj-container aj-topbar__inner">
            <!-- Left: Social + Contact -->
            <div class="aj-topbar__left">
                <!-- Social Icons -->
                <div class="aj-topbar__socials">
                    <?php foreach ( $social_icons as $key => $icon ) :
                        $url = ! empty( $socials[ $key ] ) ? $socials[ $key ] : '';
                        if ( $url === '' ) continue;
                    ?>
                        <a href="<?php echo esc_url( $url ); ?>" class="aj-topbar__social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $key ) ); ?>">
                            <?php echo $icon; // phpcs:ignore -- inline icon ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <!-- Contact Info -->
                <div class="aj-topbar__contact">
                    <?php if ( ! empty( $hdr['email'] ) ) : ?>
                        <span class="aj-topbar__item">
                            <i class="far fa-envelope"></i>
                            <?php echo esc_html( $hdr['email'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $hdr['phone'] ) ) : ?>
                        <span class="aj-topbar__item">
                            <i class="fas fa-phone"></i>
                            <?php echo esc_html( $hdr['phone'] ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right: Language, Currency, Auth -->
            <div class="aj-topbar__right">
                <!-- Language Selector -->
                <div class="aj-topbar__selector" id="aj-lang-selector">
                    <img src="https://upload.wikimedia.org/wikipedia/en/c/c3/Flag_of_France.svg" alt="FR" class="aj-topbar__flag">
                    <span>FR</span>
                    <i class="fas fa-chevron-down aj-topbar__caret"></i>
                </div>
                
                <!-- Currency Selector -->
                <div class="aj-topbar__selector" id="aj-currency-selector">
                    <span>MAD</span>
                    <i class="fas fa-chevron-down aj-topbar__caret"></i>
                </div>
                
                <!-- Auth Links -->
                <?php if ( ! empty( $hdr['show_auth_links'] ) ) : ?>
                <div class="aj-topbar__auth">
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="aj-topbar__auth-link"><?php esc_html_e( 'SE DÉCONNECTER', 'ajinsafro-traveler-home' ); ?></a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( ! empty( $hdr['login_url'] ) ? $hdr['login_url'] : wp_login_url() ); ?>" class="aj-topbar__auth-link"><?php esc_html_e( 'SE CONNECTER', 'ajinsafro-traveler-home' ); ?></a>
                        <a href="<?php echo esc_url( ! empty( $hdr['signup_url'] ) ? $hdr['signup_url'] : wp_registration_url() ); ?>" class="aj-topbar__auth-link aj-topbar__auth-link--signup"><?php esc_html_e( "S'INSCRIRE", 'ajinsafro-traveler-home' ); ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $hdr['navbar_enabled'] ) ) : ?>
    <!-- Main Navigation -->
    <nav class="aj-navbar" id="aj-navbar">
        <div class="aj-container aj-navbar__inner">

            <!-- Logo -->
            <div class="aj-navbar__logo">
                <?php if ( ! empty( $hdr['logo_url'] ) ) : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <img src="<?php echo esc_url( $hdr['logo_url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="aj-navbar__logo-img">
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="aj-navbar__brand">
                        <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button type="button" class="aj-navbar__burger aj-header__toggle" id="aj-burger" aria-label="Menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Drawer (Mobile) / Menu (Desktop) -->
            <div class="aj-drawer aj-header__drawer" id="aj-drawer" aria-hidden="true">
                <div class="aj-drawer__header">
                    <span class="aj-drawer__title"><?php esc_html_e( 'Menu', 'ajinsafro-traveler-home' ); ?></span>
                    <button type="button" class="aj-drawer__close" id="aj-drawer-close" aria-label="<?php esc_attr_e( 'Fermer', 'ajinsafro-traveler-home' ); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <?php if ( ! empty( $hdr['show_auth_links'] ) ) : ?>
                <div class="aj-drawer__auth aj-header__auth--mobile">
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="aj-auth-link aj-auth-link--block"><?php esc_html_e( 'Se déconnecter', 'ajinsafro-traveler-home' ); ?></a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( ! empty( $hdr['login_url'] ) ? $hdr['login_url'] : wp_login_url() ); ?>" class="aj-auth-link aj-auth-link--block"><?php esc_html_e( 'Se connecter', 'ajinsafro-traveler-home' ); ?></a>
                        <a href="<?php echo esc_url( ! empty( $hdr['signup_url'] ) ? $hdr['signup_url'] : wp_registration_url() ); ?>" class="aj-auth-link aj-auth-link--signup aj-auth-link--block"><?php esc_html_e( "S'inscrire", 'ajinsafro-traveler-home' ); ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="aj-navbar__menu" id="aj-nav-menu">
                <?php if ( ! empty( $hdr['menu_source'] ) && $hdr['menu_source'] === 'wp_menu' ) : ?>
                    <?php
                    $menu_location = ! empty( $hdr['wp_menu_location'] ) ? $hdr['wp_menu_location'] : 'primary';
                    if ( has_nav_menu( $menu_location ) ) {
                        wp_nav_menu( array(
                            'theme_location' => $menu_location,
                            'container'      => false,
                            'menu_class'     => 'aj-nav-list',
                            'depth'          => 2,
                            'fallback_cb'    => false,
                        ) );
                    } else {
                        wp_nav_menu( array(
                            'container'   => false,
                            'menu_class'  => 'aj-nav-list',
                            'depth'       => 2,
                            'fallback_cb' => 'wp_page_menu',
                        ) );
                    }
                    ?>
                <?php else : ?>
                    <ul class="aj-nav-list">
                        <?php
                        $nav_links = ! empty( $hdr['links'] ) && is_array( $hdr['links'] ) ? $hdr['links'] : array();
                        foreach ( $nav_links as $link ) :
                            $label    = ! empty( $link['label'] ) ? $link['label'] : '';
                            $url      = ! empty( $link['url'] ) ? $link['url'] : '#';
                            $icon     = ! empty( $link['icon'] ) ? $link['icon'] : '';
                            $children = ! empty( $link['children'] ) && is_array( $link['children'] ) ? $link['children'] : array();
                            $has_sub  = ! empty( $children );
                            $is_active = ! empty( $link['active'] );
                            $is_highlight = ! empty( $link['highlight'] );
                        ?>
                        <li class="<?php echo $has_sub ? 'aj-has-sub' : ''; ?><?php echo $is_active ? ' aj-active' : ''; ?><?php echo $is_highlight ? ' aj-highlight' : ''; ?>">
                            <a href="<?php echo esc_url( $url ); ?>" class="<?php echo $is_highlight ? 'aj-nav-highlight' : ''; ?>">
                                <?php if ( $icon ) : ?>
                                    <i class="<?php echo esc_attr( $icon ); ?>"></i>
                                <?php endif; ?>
                                <span><?php echo esc_html( $label ); ?></span>
                                <?php if ( $has_sub ) : ?>
                                    <i class="fas fa-chevron-down aj-caret"></i>
                                <?php endif; ?>
                            </a>
                            <?php if ( $has_sub ) : ?>
                                <ul class="aj-sub-menu">
                                    <?php foreach ( $children as $child ) : ?>
                                        <li><a href="<?php echo esc_url( ! empty( $child['url'] ) ? $child['url'] : '#' ); ?>"><?php echo esc_html( ! empty( $child['label'] ) ? $child['label'] : '' ); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                </div>
                
                <!-- Low Cost Button (inside drawer for mobile) -->
                <?php if ( ! empty( $hdr['lowcost_enabled'] ) ) : ?>
                <div class="aj-drawer__lowcost">
                    <a href="<?php echo esc_url( ! empty( $hdr['lowcost_url'] ) ? $hdr['lowcost_url'] : '#' ); ?>" class="aj-lowcost-btn">
                        <i class="fas fa-fire"></i>
                        <span><?php echo esc_html( ! empty( $hdr['lowcost_text'] ) ? $hdr['lowcost_text'] : 'Formule low cost' ); ?></span>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Low Cost Button (Desktop) -->
            <?php if ( ! empty( $hdr['lowcost_enabled'] ) ) : ?>
            <div class="aj-navbar__lowcost aj-header__lowcost--desktop">
                <a href="<?php echo esc_url( ! empty( $hdr['lowcost_url'] ) ? $hdr['lowcost_url'] : '#' ); ?>" class="aj-lowcost-btn aj-lowcost-btn--animate">
                    <i class="fas fa-fire aj-lowcost-btn__icon"></i>
                    <span><?php echo esc_html( ! empty( $hdr['lowcost_text'] ) ? $hdr['lowcost_text'] : 'Formule low cost' ); ?></span>
                </a>
            </div>
            <?php endif; ?>

        </div>
    </nav>
    <?php endif; ?>

</header>
