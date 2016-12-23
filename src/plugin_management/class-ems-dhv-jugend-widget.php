<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Dhv_Jugend_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
// Base ID of your widget
			'wpb_widget',

// Widget name will appear in UI
			__( 'DHV-Jugend Widget', 'ems_text_domain' ),

// Widget description
			array( 'description' => __( 'Widget mit Links zum Dashboard, Registrieren, Login,Abmelden', 'ems_text_domain' ), )
		);
	}

// Creating widget front-end
// This is where the action happens
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		?>
		<ul>
			<?php if ( is_user_logged_in() ): ?>
				<li>
					<a href="<?php echo get_permalink( get_option( Ems_Conf::EMS_NAME_PREFIX . 'eventmanagement_page' ) ); ?>">Eventverwaltung</a>
				</li>
			<?php endif; ?>
			<?php if ( is_user_logged_in() && ( current_user_can( 'read_event' ) || current_user_can( "read_" . Ems_Conf::EMS_NAME_PREFIX . "event" ) ) ): ?>
				<li>
					<a href="<?php echo get_permalink( get_option( Ems_Conf::EMS_NAME_PREFIX . 'partcipant_list_page' ) ); ?>">Teilnehmerlisten</a>
				</li>
			<?php endif; ?>
			<?php if ( is_user_logged_in() && ( current_user_can( 'read_event' ) || current_user_can( "read_" . Ems_Conf::EMS_NAME_PREFIX . "event" ) ) ): ?>
				<li>
					<a href="<?php echo get_permalink( get_option( Ems_Conf::EMS_NAME_PREFIX . 'event_statistic_page' ) ); ?>">Eventstatistiken</a>
				</li>
				<li>
					<a target="_blank" href="https://drive.google.com/folderview?id=0B6sRvq58A9UtQVpqbTZkbTE3eUE&usp=sharing">Google Drive</a>
				</li>
			<?php endif; ?>
			<!--			--><?php //if ( is_user_logged_in() && ( current_user_can( 'read_event' ) || current_user_can( "read_" . Ems_Conf::EMS_NAME_PREFIX . "event" ) ) ): ?>
			<!--				<li>-->
			<!--					<a href="--><?php //echo get_permalink( 1153 ); ?><!--">Leitlinien/Protokolle</a>-->
			<!--				</li>-->
			<!--			--><?php //endif; ?>
			<?php if ( ! is_user_logged_in() || current_user_can( 'manage_options' ) || ( current_user_can( 'read_event' ) || current_user_can( "read_" . Ems_Conf::EMS_NAME_PREFIX . "event" ) ) ): ?>
				<?php wp_register(); ?>
			<?php endif; ?>
			<?php if ( is_user_logged_in() ): ?>
				<li><a href="<?php echo admin_url( 'profile.php' ); ?>">Profil editieren</a></li>
			<?php endif;
			$redirect = home_url();
			if ( ! is_user_logged_in() ):
				$redirect = get_permalink();
			endif; ?>
			<li><?php wp_loginout( $redirect ); ?></li>
			<?php wp_meta(); ?>
		</ul>
	<?php
	}

// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'New title', 'wpb_widget_domain' );
		}
// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
	<?php
	}

// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} 