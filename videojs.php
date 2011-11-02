<?php
if (!class_exists('cfct_module_image')) {
	require_once(CFCT_BUILD_DIR.'modules/image/image.php');
}
if (!class_exists('cfct_module_videojs') && class_exists('cfct_module_image')) {
	class cfct_module_videojs extends cfct_module_image {
		protected $default_video_height = 264;	
		protected $default_video_width = 640;	
		/**
		 * Set up the module
		 */
		public function __construct() {
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginPath		= WP_PLUGIN_DIR . '/' . $this->pluginDir;
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;		
	
			$opts = array(
				'url' => $this->pluginUrl, 
				'view' => 'wp-cb-videojs-module/view.php',
				'description' => __('Allows to add a video to a page', 'carrington-build'),
				'icon' => 'wp-cb-videojs-module/icon.png'
			);
			cfct_build_module::__construct('cfct-module-videojs', __('Video', 'carrington-build'), $opts);
			add_action('get_header', array(&$this, 'videojs_get_header'));
			add_shortcode('video', array(&$this, 'video_shortcode'));			
		}
		
		public function videojs_get_header() {
			wp_enqueue_style('video-js', $this->get_url()  . '/video-js/video-js.css' );			
			wp_enqueue_script('video-js', $this->get_url() . '/video-js/video.js');
			wp_enqueue_script('video-js-setup', $this->get_url() . '/video-js/setup.js', array('jquery', 'video-js'), '1.0');
			
		}

		function video_shortcode($atts) {
			extract(shortcode_atts(array(
				'mp4' => '',
				'webm' => '',
				'ogg' => '',
				'poster' => '',
				'width' => '',
				'height' => '',
				'preload' => false,
				'autoplay' => false,
			), $atts));

			// MP4 Source Supplied
			if ($mp4) {
				$mp4_source = '<source src="'.$mp4.'" type=\'video/mp4; codecs="avc1.42E01E, mp4a.40.2"\' />';
				$mp4_link = '<a href="'.$mp4.'">MP4</a>';
			}

			// WebM Source Supplied
			if ($webm) {
				$webm_source = '<source src="'.$webm.'" type=\'video/webm; codecs="vp8, vorbis"\' />';
				$webm_link = '<a href="'.$webm.'">WebM</a>';
			}

			// Ogg source supplied
			if ($ogg) {
				$ogg_source = '<source src="'.$ogg.'" type=\'video/ogg; codecs="theora, vorbis"\' />';
				$ogg_link = '<a href="'.$ogg.'">Ogg</a>';
			}

			if ($poster) {
				$poster_attribute = 'poster="'.$poster.'"';
				$flow_player_poster = '"'.$poster.'", ';
				$image_fallback = 
<<<_end_
<!-- Image Fallback -->
<img src="$poster" width="$width" height="$height" alt="Poster Image" title="No video playback capabilities." />
_end_;
			}

			if ($preload) {
				$preload_attribute = 'preload="auto"';
				$flow_player_preload = ',"autoBuffering":true';
			} else {
				$preload_attribute = 'preload="none"';
				$flow_player_preload = ',"autoBuffering":false';
			}

			if ($autoplay) {
				$autoplay_attribute = "autoplay";
				$flow_player_autoplay = ',"autoPlay":true';
			} else {
				$autoplay_attribute = "";
				$flow_player_autoplay = ',"autoPlay":false';
			}

			$videojs .= 
<<<_end_
<!-- Begin VideoJS -->
<div class="video-js-box" style="display:none;">
  <!-- Using the Video for Everybody Embed Code http://camendesign.com/code/video_for_everybody -->
  <video class="video-js" width="{$width}" height="{$height}" {$poster_attribute} controls {$preload_attribute} {$autoplay_attribute}>
  {$mp4_source}
  {$webm_source}
  {$ogg_source}
  <!-- Flash Fallback. Use any flash video player here. Make sure to keep the vjs-flash-fallback class. -->
  <object class="vjs-flash-fallback" wmode="opaque" width="{$width}" height="{$height}" type="application/x-shockwave-flash"
	data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
	<param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
	<param name="allowfullscreen" value="true" />
	<param name="wmode" value="opaque">
	<param name="flashvars" value='config={"playlist":[$flow_player_poster{"url": "$mp4" $flow_player_autoplay $flow_player_preload }], "play":{"replayLabel":null}}' />
	{$image_fallback}
  </object>
</video>
</div>
<!-- End VideoJS -->
_end_;
			return $videojs;
		}

		
		/**
		 * Display the module content in the Post-Content
		 * 
		 * @param array $data - saved module data
		 * @return array string HTML
		 */
		public function display($data) {
			$video = '';
			$poster = '';
			if (!empty($data[$this->get_field_name('video_id')])) {
				$size = (!empty($data[$this->get_field_name('video_id').'-size']) ? $data[$this->get_field_name('image_id').'-size'] : 'thumbnail');
				$video = wp_get_attachment_url($data[$this->get_field_name('video_id')]);
				$poster = wp_get_attachment_url($data[$this->get_field_name('image_id')]);	
				$url = $this->get_link_url($data);
				$width = (!empty($data[$this->get_field_name('video-width')]) ? intval($data[$this->get_field_name('video-width')]) : $default_video_width);
				$height = (!empty($data[$this->get_field_name('video-height')]) ? intval($data[$this->get_field_name('video-height')]) : $default_video_height);
				
				$file_extension = substr(strrchr($video,'.'),1);
				$mp4_attr = $file_extension.'="'.$video.'" ';
				$poster_attr = 'poster="'.$poster.'" '; 
				$width_attr = 'width="'.$width.'" ';
				$height_attr = 'height="'.$height.'"    ';
				$vidoejs_html = '[video '.$mp4_attr.$width_attr.$height_attr.$poster_attr.'] [/video]';
			} else {
				$vidoejs_html = null;
			}
			
			global $cfct_build;			
	
			$cfct_build->loaded_modules[$this->basename] = $this->pluginPath;
			$cfct_build->module_paths[$this->basename] = $this->pluginPath;
			$cfct_build->module_urls[$this->basename] = $this->pluginUrl;
			
			return $this->load_view($data, compact('vidoejs_html', 'url'));
		}
		
		private function img_size($data = array()) {
			return (!empty($data[$this->get_field_id('link_img_size')]) ? $data[$this->get_field_id('link_img_size')] : 'large');
		}		
	    private function link_target($data = array()) {
			return (!empty($data[$this->get_field_id('link_target')]) ? $data[$this->get_field_id('link_target')] : 'none');
		}
		/**
		 * Build the admin form
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form($data) {

			// tabs
			$image_selector_tabs = array(
				$this->id_base.'-post-video-wrap' => __("Video", 'carrington-build'),
				$this->id_base.'-post-image-wrap' => __("Preview Image", 'carrington-build')
			);
			
			// set active tab
			$active_tab = $this->id_base.'-post-video-wrap';
			
			// set default link target
			$link_target = $this->link_target($data);
			
			// set default image size
			$link_img_size = $this->img_size($data);
			
			$html = '
				<fieldset>
					<!-- video selector tabs -->
					<div id="'.$this->id_base.'-video-selectors">
						<!-- tabs -->
						'.$this->cfct_module_tabs($this->id_base.'-video-selector-tabs', $image_selector_tabs, $active_tab).'
						<!-- /tabs -->
					
						<div class="cfct-module-tab-contents">
							<!-- select an image from this post -->
							<div id="'.$this->id_base.'-post-video-wrap" '.($active_tab == $this->id_base.'-post-video-wrap' ? ' class="active"' : null).'>
								'.$this->post_video_selector($data).'
							</div>
							<!-- / select an image from this post -->
					
							<!-- select an image from this post -->
							<div id="'.$this->id_base.'-post-image-wrap" '.($active_tab == $this->id_base.'-post-image-wrap' ? ' class="active"' : null).'>
								'.$this->post_image_selector($data).'
							</div>
							<!-- / select an image from this post -->
							
						</div>

						<fieldset class="cfct-ftl-border">
							<legend>Video Size</legend>
							<table class="'.$this->id_base.'-video-size">
								<tr>
									<td align="right">
										<label for="'.$this->get_field_id('video-width').'">'.__('Width', 'carrington-build').'</label>
									</td>
									<td>
										<input type="text" name="'.$this->get_field_name('video-width').'" id="'.$this->get_field_id('video-width').'" value="'.(!empty($data[$this->get_field_name('video-width')]) ? esc_attr($data[$this->get_field_name('video-width')]) : $this->default_video_width).'" />
										<span>pixels</span>										
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="'.$this->get_field_id('video-height').'">'.__('Height', 'carrington-build').'</label>
									</td>
									<td>
										<input type="text" name="'.$this->get_field_name('video-height').'" id="'.$this->get_field_id('video-height').'" value="'.(!empty($data[$this->get_field_name('video-height')]) ? esc_attr($data[$this->get_field_name('video-height')]) : $this->default_video_height).'" />
										<span>pixels</span>										
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<!-- / image selector tabs -->
				</fieldset>
				';
				
			return $html;
		}
		
		// helpers
		function post_video_selector($data = false, $multiple = false) {
			$ajax_args = cf_json_decode(stripslashes($_POST['args']), true);
			
			$selected = 0;
			if (!empty($data[$this->get_field_id('post_video')])) {
				$selected = $data[$this->get_field_id('post_video')];
			}

			$selected_size = null;
			if (!empty($data[$this->get_field_name('post_video').'-size'])) {
				$selected_size = $data[$this->get_field_name('post_video').'-size'];
			}

			$args = array(
				'field_name' => 'post_video',
				'selected_image' => $selected,
				'selected_size' => $selected_size,
				'allow_multiple' => $multiple,
				'post_id' => $ajax_args['post_id']
			);
			return $this->image_selector('video', $args);
		}

		public function image_selector($type = 'post', $args = array()) {
			$args = array_merge(array(
				'post_id' => null,
				'field_name' => null,
				'selected_image' => null,
				'selected_size' => null,
				'allow_multiple' => null,
				'image_size' => 'thumbnail',		
				'parent_class' => null,
				'image_class' => null,			
				'selected_image_class' => null  
			), $args);
			if ($type == 'post') {
				return $this->_post_image_selector($args);
			}
			else {
				return $this->_post_video_selector($args);
			}
		}
		
		public function _post_video_selector($args) {
			if (empty($args['post_id'])) {
				return false;
			}
			
			$attachment_args = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'video',
				'numberposts' => -1,
				'post_status' => 'inherit',
				'post_parent' => $args['post_id'],
				'order' => 'ASC'
			); 

			$attachments = get_posts($attachment_args); 

			if (count($attachments)) {
				$id = $this->id_base.'-'.$args['field_name'].'-image-select-items-list';
				
				$class = 'cfct-post-image-select cfct-image-select-items-list '.$this->_image_list_dir_class($args);
				if (!empty($args['allow_multiple']) && $args['allow_multiple'] == true) {
					$class .= ' cfct-post-image-select-multiple';
					$note = __('Select one or more Videos', 'carrington-build');
				}
				else {
					$class .= ' cfct-post-image-select-single';
					$note = __('Select an Video', 'carrington-build');
				}
				
				$html = '
					<p class="cfct-image-select-note">'.$note.'</p>
					<div id="'.$id.'" class="'.$class.'">
						<div>
							'.$this->_image_list($attachments, $args).'
							<input type="hidden" name="'.$this->get_field_name($args['field_name']).'" id="'.$this->get_field_id($args['field_name']).'" value="'.$args['selected_image'].'" />
						</div>
					</div>
					';
			}
			else {
				$html = '<div class="cfct-image-select-no-images">'.__('You don\'t have any videos attached to this post yet.<br/> Upload an video to this post and come back.', 'carrington-build').'</div>';
			}
			return apply_filters($this->id_base.'-image-select-html', $html, $args);
		}

		public function _post_image_selector($args) {
			if (empty($args['post_id'])) {
				return false;
			}
			
			$attachment_args = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'numberposts' => -1,
				'post_status' => 'inherit',
				'post_parent' => $args['post_id'],
				'order' => 'ASC'
			); 

			$attachments = get_posts($attachment_args); 

			if (count($attachments)) {
				$id = $this->id_base.'-'.$args['field_name'].'-image-select-items-list';
				
				$class = 'cfct-post-image-select cfct-image-select-items-list '.$this->_image_list_dir_class($args);
				if (!empty($args['allow_multiple']) && $args['allow_multiple'] == true) {
					$class .= ' cfct-post-image-select-multiple';
					$note = __('Select one or more Images', 'carrington-build');
				}
				else {
					$class .= ' cfct-post-image-select-single';
					$note = __('Select an Image', 'carrington-build');
				}
				
				$html = '
					<p class="cfct-image-select-note">'.$note.'</p>
					<div id="'.$id.'" class="'.$class.'">
						<div>
							'.$this->_image_list($attachments, $args).'
							<input type="hidden" name="'.$this->get_field_name($args['field_name']).'" id="'.$this->get_field_id($args['field_name']).'" value="'.$args['selected_image'].'" />
						</div>
					</div>
					';
			}
			else {
				$html = '<div class="cfct-image-select-no-images">'.__('You don\'t have any images attached to this post yet.<br/> Upload an image to this post and come back.', 'carrington-build').'</div>';
			}
			return apply_filters($this->id_base.'-image-select-html', $html, $args);
		}
		
		public function update($new_data, $old_data) {
			if (!empty($new_data[$this->get_field_name('post_image')])) {
				$new_data[$this->get_field_name('image_id')] = $new_data[$this->get_field_name('post_image')];
				$new_data[$this->get_field_name('image_id').'-size'] = $new_data[$this->get_field_name('post_image').'-size'];
			}
			
			if (!empty($new_data[$this->get_field_name('post_video')])) {
				$new_data[$this->get_field_name('video_id')] = $new_data[$this->get_field_name('post_video')];
				$new_data[$this->get_field_name('video_id').'-size'] = $new_data[$this->get_field_name('post_video').'-size'];
			}
			return $new_data;
		}
		
		public function admin_css() {
			$css = parent::admin_css();
			$css .= '
				#'.$this->id_base.'-edit-form fieldset .'.$this->id_base.'-video-size input[type=text] {
					width: 50px;
					height: 29px;
					text-align: right;
				}
			';
			return $css;
		}		

		public function text($data) {
			$image = '';
			if (!empty($data[$this->get_field_name('video_id')])) {
				$image = get_post($data[$this->get_field_name('video_id')]);
				if ($image) {
					$image = $image->post_title;
				}
			}
			return esc_html($image);
		}
		
	}
	
	// register the module with Carrington Build
	cfct_build_register_module('cfct-module-videojs', 'cfct_module_videojs');
}

?>