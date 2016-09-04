<?php

/**
 * Plugin Name: Brainstorm Debug
 * Plugin URI: http://www.ultimatebeaver.com/
 * Description: Check memory usage.
 * Version: 1.0.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 */

/**
 * BrainstormForce Debug setup
 *
 * @since 1.0.0
 */

class BrainstormForce_Debug {

	private static $instance;

	/**
	*  Initiator
	*/
	public static function get_instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BrainstormForce_Debug();
		}
		return self::$instance;
	}

	/**
	*  Constructor
	*/
	public function __construct() {
	}

	public function init() {

		add_action( 'wp_footer', array( $this, 'add_html' ) );
		add_action( 'admin_footer', array( $this, 'add_html' ) );

		add_action( 'init', array( $this, 'process' ) );
		add_action( 'wp_after_admin_bar_render', array( $this, 'process' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_babel_type' ), 10, 3 );

	}

	function add_html() {
		?>
			<style type="text/css">
				#bsf_debug_info {
					overflow: hidden;
					position: fixed;
				    bottom: 0;
				    right: 0;
				    text-align: left;
				    margin: 30px 15px;
				    padding: 1em;
				    border: 0px;
				    outline: 0px;
				    font-size: 14px;
				    font-family: monospace;
				    vertical-align: baseline;
				    max-width: 100%;
				    overflow: auto;
				    color: rgb(248,248,242);
				    direction: ltr;
				    word-spacing: normal;
				    line-height: 1.5;
				    border-radius: 0.3em;
				    word-wrap: normal;
				    letter-spacing: 0.266667px;
				    background: rgb(61,69,75);
				}
				.vl_pre {
					text-align: left;
					margin: 30px 15px;
					padding: 1em;
					border: 0px;
					outline: 0px;
					font-size: 14px;
					font-family: monospace;
					vertical-align: baseline;
					max-width: 100%;
					overflow: auto;
					color: rgb(248,248,242);
					direction: ltr;
					word-spacing: normal;
					line-height: 1.5;
					border-radius: 0.3em;
					word-wrap: normal;
					letter-spacing: 0.266667px;
					background: rgb(61,69,75);
				}
				.bsf-extra-info {
				    font-size: 12px;
				    opacity: 0;
				    visibility: hidden;
				    width: 213px;
				    background: #fff;
				    color: #3d454b;
				    font-family: open sans;
				    position: absolute;
				    padding: 10px;
				    font-weight: bold;
				    text-align: left;
				    line-height: 1.5em;
				    border-radius: 4px;
				    right: -100px;
				    top: -70px;
				    transition: all ease-in-out 0.3s;
				}
				.dashicons-info {
					position: relative;
					cursor: pointer;
				}
				.dashicons-info:hover > .bsf-extra-info {
				    opacity: 1;
				    visibility: visible;
    				top: -63px;
				}
			</style>
			<div id="bsf_debug_info">
				<div id="bsf_debug_info-init"></div>
				<div id="bsf_debug_info-afterload"></div>
			</div>
		<?php
	}

	function load_scripts() {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'bsf-debug-react.js' , plugin_dir_url( __FILE__ ) . 'build/react.js', null, null, true );
		wp_enqueue_script( 'bsf-debug-react-dom' , plugin_dir_url( __FILE__ ) . 'build/react-dom.js', null, null, true );
		wp_enqueue_script( 'bsf-debug-browser.min' , 'https://unpkg.com/babel-core@5.8.38/browser.min.js', null, null, true );
		// wp_enqueue_script( 'bsf-debug' , plugin_dir_url( __FILE__ ) . 'build/brainstorm-debug.js', null, null, true );
		// wp_register_script( 'bsf-debug-browser.min' , plugin_dir_url( __FILE__ ) . 'build/browser.min.js', null, null, true );
	}
	function add_babel_type( $tag, $handle, $src ) {

		if ( $handle !== 'bsf-debug' ) {
			return $tag;
		}

		return '<script src="' . $src . '" type="text/babel"></script>' . "\n";
	}


	//	Required Memory for UABB
	function process( $status = '' ) {

		$total_memory              = ini_get('memory_limit'); 		//	Total Memory
		$available_memory          = memory_get_peak_usage(true);	//	Used Memory
		$initial_required_required = 14999999; 						//	Initial required Memory

		if( preg_match('/^(\d+)(.)$/', $total_memory, $matches ) ) {

		    switch( $matches[2] ) {
		    	case 'K': 	$total_memory = $matches[1] * 1024; 				break;
		    	case 'M': 	$total_memory = $matches[1] * 1024 * 1024; 			break;
		    	case 'G': 	$total_memory = $matches[1] * 1024 * 1024 * 1024; 	break;
		    }
		}

		//	Convert = Bytes to MB
		$__total_memory              = ( $total_memory / 1024 / 1024 );
		$__available_memory          = ( $available_memory / 1024 / 1024 );
		$__initial_required_required = ( $initial_required_required / 1024 / 1024 );

		?>

		<script type="text/babel">
			var NopeHere = React.createClass({

			  	render: function() {
			    	return (
			    		<?php
								echo "<div className='bsf_debug vl_pre'>";
								echo 'Hook: ' . current_action() . ' <br/>';
								echo '________________________________________<br/>';
								echo 'Total: ' . $__total_memory . ' MB <br/>';
								echo 'Used: ' . $__available_memory . ' MB <br/>';
								echo '________________________________________<br/>';
								echo 'Remaining: ' . ( $__total_memory - $__available_memory ) . ' MB ';

								echo '<i className="dashicons dashicons-info"><span className="bsf-extra-info">';
								echo ' In KB : ' . ( ( $__total_memory - $__available_memory ) * 1024 ) . ' <br/>';
								echo ' In Bytes : ' . ( ( $__total_memory - $__available_memory ) * 1024 * 1024 );
								echo '</span></i><br/>';
								echo '________________________________________<br/>';
								// echo 'Initial Required: ' . $__initial_required_required . ' MB <br/>';
								echo "</div>";
				      	?>
			    	);
			  	}

			});

			<?php
			if( current_action() == 'wp_after_admin_bar_render' ) {
				$HookName = 'bsf_debug_info-afterload';
			}
			if( current_action() == 'init' ) {
				$HookName = 'bsf_debug_info-init';
			}
			?>

			ReactDOM.render(
				<NopeHere />,
				document.getElementById("<?php echo $HookName; ?>")
			);
		</script>

		<?php

		if( $total_memory - $available_memory <= $initial_required_required ) {
			return true;
		} else {
			return false;
		}
	}

}

$BrainstormForce_Debug = new BrainstormForce_Debug();
$BrainstormForce_Debug->init();
