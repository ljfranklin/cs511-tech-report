    
	<header id="masthead" class="site-header" role="banner">
		<div class="csse-banner">
			<a href="http://csse.usc.edu/">
				<img src="<?php echo get_stylesheet_directory_uri() . '/images/cropped-usc-viterbi-logo5-color_fixed-1024x98.png'; ?>">
			</a>
		</div>
		<div class="header-main">
			<div class="header-top">
				<?php if (get_theme_mod( 'ridizain_logo_image' )) : ?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<img src="<?php echo get_theme_mod( 'ridizain_logo_image' ); ?>" alt="<?php esc_html(get_theme_mod( 'ridizain_logo_alt_text' )); ?>" />
						</a>
					</h1>
				<?php else : ?>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php endif; ?>

					<nav id="primary-navigation" class="site-navigation primary-navigation" role="navigation">
						<h1 class="menu-toggle"><?php _e( 'Primary Menu', 'ridizain' ); ?></h1>
						<a class="screen-reader-text skip-link" href="#content"><?php _e( 'Skip to content', 'ridizain' ); ?></a>
						<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
				
						<?php if (is_user_logged_in() === false) : ?>
						<span class="page_item">
							<a href="<?php echo wp_login_url(); ?>">
								<span class="genericon genericon-user"></span> Login
							</a>
						</span>
						<?php else : ?>
						<span class="page_item">
							<a href="<?php echo admin_url('admin.php?page=upload-paper'); ?>">
								<span class="genericon genericon-cloud-upload"></span> Upload Paper
							</a>
						</span>
						<span class="page_item">
							<a href="<?php echo wp_logout_url(); ?>">
								<span class="genericon genericon-user"></span> Logout
							</a>
						</span>
						<?php endif; ?>
					</nav>
				
					<span onclick="document.getElementsByClassName('search-form')[0].submit()" class="search-btn genericon genericon-search"></span>
				</div>
				<div class="header-bottom">
					<?php
						$description = get_bloginfo( 'description', 'display' );
						if ( ! empty ( $description ) ) :
					?>
					<h2 class="site-description"><?php echo esc_html( $description ); ?></h2>
					<?php endif; ?>
					<?php get_search_form(); ?>
				</div>
		</div>
		
	</header><!-- #masthead -->
