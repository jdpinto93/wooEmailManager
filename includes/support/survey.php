<?php
if ( ! class_exists( 'VillaThemeDeactivateSurvey' ) ) :

	class VillaThemeDeactivateSurvey {

		public function __construct() {
			add_action( 'admin_footer', array( $this, 'deactivate_scripts' ) );
		}

		private function get_uninstall_reasons() {

			$reasons = array(
				array(
					'id'          => 'could_not_understand',
					'text'        => __( 'I couldn\'t understand how to make it work', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Would you like us to assist you?', 'viwec-email-template-customizer' )
				),
				array(
					'id'          => 'found_better_plugin',
					'text'        => __( 'I found a better plugin', 'viwec-email-template-customizer' ),
					'type'        => 'text',
					'placeholder' => __( 'Which plugin?', 'viwec-email-template-customizer' )
				),
				array(
					'id'          => 'not_have_that_feature',
					'text'        => __( 'The plugin is great, but I need specific feature that you don\'t support', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Could you tell us more about that feature?', 'viwec-email-template-customizer' )
				),
				array(
					'id'          => 'is_not_working',
					'text'        => __( 'The plugin is not working', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Could you tell us a bit more whats not working?', 'viwec-email-template-customizer' )
				),
				array(
					'id'          => 'looking_for_other',
					'text'        => __( 'It\'s not what I was looking for', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => 'Could you tell us a bit more?'
				),
				array(
					'id'          => 'did_not_work_as_expected',
					'text'        => __( 'The plugin didn\'t work as expected', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => __( 'What did you expect?', 'viwec-email-template-customizer' )
				),
				array(
					'id'          => 'other',
					'text'        => __( 'Other', 'viwec-email-template-customizer' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Could you tell us a bit more?', 'viwec-email-template-customizer' )
				),
			);

			return $reasons;
		}

		public function deactivate_scripts() {

			global $pagenow;
			if ( 'plugins.php' != $pagenow ) {
				return;
			}
			$reasons = $this->get_uninstall_reasons();
			?>
            <div class="villatheme-deactivate-modal" id="villatheme-deactivate-survey-modal">
                <div class="villatheme-deactivate-modal-wrap">
                    <div class="villatheme-deactivate-modal-header">
                        <h3><?php esc_html_e( 'If you have a moment, please let us know why you are deactivating:', 'viwec-email-template-customizer' ); ?></h3>
                    </div>
                    <div class="villatheme-deactivate-modal-body">
                        <ul class="reasons">
							<?php foreach ( $reasons as $reason ) { ?>
                                <li data-type="<?php echo esc_attr( $reason['type'] ); ?>" data-placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>">
                                    <label>
                                        <input type="radio" name="selected-reason" value="<?php echo esc_attr( $reason['id'] ); ?>">
										<?php echo esc_html( $reason['text'] ); ?>
                                    </label>
                                </li>
							<?php } ?>
                        </ul>
                    </div>
                    <div class="villatheme-deactivate-modal-footer">
                        <a href="#" class="dont-bother-me"><?php esc_html_e( 'I rather wouldn\'t say', 'viwec-email-template-customizer' ); ?></a>
                        <button class="button-primary villatheme-deactivate-submit"><?php esc_html_e( 'Submit & Deactivate', 'viwec-email-template-customizer' ); ?></button>
                        <button class="button-secondary villatheme-model-cancel"><?php esc_html_e( 'Cancel', 'viwec-email-template-customizer' ); ?></button>
                    </div>
                </div>
            </div>

            <style type="text/css">
                .villatheme-deactivate-modal {
                    position: fixed;
                    z-index: 99999;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: none;
                }

                .villatheme-deactivate-modal.modal-active {
                    display: block;
                }

                .villatheme-deactivate-modal-wrap {
                    width: 50%;
                    position: relative;
                    margin: 10% auto;
                    background: #fff;
                }

                .villatheme-deactivate-modal-header {
                    border-bottom: 1px solid #eee;
                    padding: 8px 20px;
                }

                .villatheme-deactivate-modal-header h3 {
                    line-height: 150%;
                    margin: 0;
                }

                .villatheme-deactivate-modal-body {
                    padding: 5px 20px 20px 20px;
                }

                .villatheme-deactivate-modal-body .input-text, .villatheme-deactivate-modal-body textarea {
                    width: 75%;
                }

                .villatheme-deactivate-modal-body .reason-input {
                    margin-top: 5px;
                    margin-left: 20px;
                }

                .villatheme-deactivate-modal-footer {
                    border-top: 1px solid #eee;
                    padding: 12px 20px;
                    text-align: right;
                }
            </style>
            <script type="text/javascript">
                (function ($) {
                    $(function () {
                        var modal = $('#villatheme-deactivate-survey-modal');
                        var deactivateLink = '';
                        $('#the-list').on('click', 'a#deactivate-email-template-customizer-for-woo', function (e) {
                            e.preventDefault();
                            modal.addClass('modal-active');
                            deactivateLink = $(this).attr('href');
                            modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                        });
                        modal.on('click', 'button.villatheme-model-cancel', function (e) {
                            e.preventDefault();
                            modal.removeClass('modal-active');
                        });
                        modal.on('click', 'input[type="radio"]', function () {
                            var parent = $(this).parents('li:first');
                            modal.find('.reason-input').remove();
                            var inputType = parent.data('type'),
                                inputPlaceholder = parent.data('placeholder'),
                                reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';

                            if (inputType !== '') {
                                parent.append($(reasonInputHtml));
                                parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                            }
                        });

                        modal.on('click', 'button.villatheme-deactivate-submit', function (e) {
                            e.preventDefault();
                            var button = $(this);
                            if (button.hasClass('disabled')) {
                                return;
                            }
                            let url = 'https://script.google.com/macros/s/AKfycbxkQO1eTmttYm1uNwN_pxenA9JEYbDo8PWumGZPvk29G2VIDi59/exec';
                            var $radio = $('input[type="radio"]:checked', modal);
                            var $selected_reason = $radio.parents('li:first'),
                                $input = $selected_reason.find('textarea, input[type="text"]');
                            let reason_id = (0 === $radio.length) ? '' : $radio.val();
                            let reason_info = (0 !== $input.length) ? $input.val().trim() : '';
                            let date = new Date(Date.now()).toLocaleString().split(',')[0];

                            if (reason_id) {
                                $.ajax({
                                    url: `${url}?date=${date}&${reason_id}=1&reason_info=${reason_info}`,
                                    type: 'GET',
                                    beforeSend: function () {
                                        button.addClass('disabled');
                                        button.text('Processing...');
                                    },
                                    complete: function () {
                                        window.location.href = deactivateLink;
                                    }
                                });
                            } else {
                                window.location.href = deactivateLink;
                            }

                        });
                    });
                }(jQuery));
            </script>
			<?php
		}
	}

endif;

new VillaThemeDeactivateSurvey();