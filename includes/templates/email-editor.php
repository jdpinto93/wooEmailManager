<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
$arrow = $admin_bar_stt ? 'left' : 'right';
?>
<br class="clear"/>
<div>
    <div id="viwec-email-editor-container">
        <div id="viwec-control-panel">
            <div class="viwec-control-panel-fixed">
                <div class="vi-ui three item stackable tabs menu">
                    <a class="active item" data-tab="components"><?php esc_html_e( 'Components', 'viwec-email-template-customizer' ); ?></a>
                    <a class="item" data-tab="editor"><?php esc_html_e( 'Editor', 'viwec-email-template-customizer' ); ?></a>
                    <a class="item" data-tab="custom_css"><?php esc_html_e( 'Custom CSS', 'viwec-email-template-customizer' ); ?></a>
                </div>
                <div class="viwec-scroll">
                    <div class="vi-ui bottom attached active tab" data-tab="components">
                        <div id="viwec-element-search">
                            <i class="dashicons dashicons-search"></i>
                            <input type="text" class="viwec-search" placeholder="<?php esc_html_e( 'Search element', 'viwec-email-template-customizer' ); ?>">
                        </div>

                        <div id="viwec-components-list">

                        </div>
                    </div>

                    <div class="vi-ui bottom attached tab " data-tab="editor">
                        <div id="viwec-attributes-list">

                        </div>
                    </div>

                    <div class="vi-ui bottom attached tab " data-tab="custom_css">
                        <div id="viwec-custom-css">
                            <textarea rows="10" name="viwec_custom_css" value=""><?php echo esc_html( $custom_css ) ?></textarea>
                        </div>
                    </div>

                </div>

                <div id="viwec-main-actions">
                    <div class="viwec-main-actions-inner">
                        <div class="viwec-actions-front">

                            <a class="viwec-exit-to-dashboard " href="<?php echo esc_url( admin_url( 'edit.php?post_type=viwec_template' ) ) ?>">
                                <i class="dashicons dashicons-arrow-left"
                                   title=" <?php esc_html_e( 'Exit to DashBoard', 'viwec-email-template-customizer' ); ?>">
                                </i>
                            </a>

                            <i class="dashicons dashicons-arrow-<?php echo esc_attr( $arrow ) ?> viwec-toggle-admin-bar"
                               title="<?php esc_attr_e( 'Toggle admin menu', 'viwec-email-template-customizer' ) ?>">
                            </i>

                            <a class="viwec-add-new" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=viwec_template' ) ) ?>">
                                <i class="dashicons dashicons-plus" title="<?php esc_attr_e( 'Add new', 'viwec-email-template-customizer' ); ?>">
                                </i>
                            </a>
                            <a class="viwec-duplicate-post"
                               href="<?php echo esc_url( admin_url( 'post.php?action=viwec_duplicate&id=' ) . get_the_ID() ) ?>">
                                <i class="dashicons dashicons-admin-page"
                                   title=" <?php esc_html_e( 'Copy to draft', 'viwec-email-template-customizer' ); ?>">
                                </i>

                            </a>
							<?php
							if ( current_user_can( 'delete_post', $post->ID ) ) {
								echo sprintf( "<a class='viwec-trash-post' href='%1s' title='%2s'><i class='dashicons dashicons-trash'> </i></a>",
									esc_attr( get_delete_post_link( $post->ID ) ), esc_attr__( 'Move to trash', 'viwec-email-template-customizer' ) );
							}
							?>
                            <div id="viwec-publishing-action">
								<?php
								if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
									?>
                                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ); ?>"/>
                                    <button type="submit" name="publish" id="publish" value="Publish"
                                            class=""><?php esc_attr_e( 'Publish' ); ?></button>
									<?php
								} else {
									?>
                                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ); ?>"/>
                                    <button type="submit" name="save" id="publish" value="Update" class=""><?php esc_attr_e( 'Update' ); ?></button>
									<?php
								}
								?>
                                <button type="button" class="viwec-show-sub-actions">
                                    <i class="dashicons dashicons-arrow-up"></i>
                                </button>
                            </div>
                        </div>

                        <div class="viwec-actions-back">
                            <button type="submit" name="save" id="save-post" class="viwec-save-draft"
                                    value="save_draft"><?php esc_attr_e( 'Save Draft' ); ?></button>
                        </div>
                        <input type="hidden" name="post_status" value="publish">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="viwec-email-editor-wrapper" style="background-size: cover;background-position: center top; background-repeat: no-repeat;">
        <div class="viwec-edit-bgcolor-btn">
            <span class="vi-ui button mini">
                <i class="dashicons dashicons-edit"></i>
                <span><?php esc_html_e( 'Background', 'viwec-email-template-customizer' ); ?></span>
            </span>
        </div>
        <div id="viwec-email-editor-content" class="viwec-sortable viwec-direction-<?php echo esc_attr( $direction ) ?>">
        </div>
        <div id="viwec-quick-add-layout">
            <div class="dashicons dashicons-plus viwec-quick-add-layout-btn"
                 title="<?php esc_html_e( 'Select layout', 'viwec-email-template-customizer' ); ?>"></div>
            <div class="viwec-layout-list"></div>
        </div>
    </div>

    <div id="viwec-notice-box">
    </div>
</div>


<div id="viwec-templates">

    <script id="viwec-input-handle-outer" type="text/html">
        <div class="viwec-layout-handle-outer">
            <div class="left">
                <span class="dashicons dashicons-move viwec-move-row-btn" title="Move"></span>
                <span class="dashicons dashicons-edit viwec-edit-outer-row-btn" title="Edit row outer"></span>
                <span class="dashicons dashicons-admin-page viwec-duplicate-row-btn" title="Duplicate"></span>
            </div>
            <div class="right">
                <span class="dashicons dashicons-welcome-add-page viwec-copy-row-btn" title="Copy"></span>
                <span class="dashicons dashicons-arrow-down-alt viwec-paste-row-btn" title="Paste"></span>
                <span class="dashicons dashicons-no-alt viwec-delete-row-btn" title="Delete"></span>
            </div>
        </div>
    </script>

    <script id="viwec-input-handle-inner" type="text/html">
        <div class="viwec-layout-handle-inner">
            <!--            <span class="dashicons dashicons-edit viwec-edit-inner-row-btn" title="Edit row inner"></span>-->
        </div>
    </script>

    <script id="viwec-block" type="text/html">
        <div class="viwec-block">
            <div class="viwec-layout-row" data-type="{%=type%}" data-cols="{%=colsQty%}"
                 style="padding: 15px 35px; background-color: #ffffff; background-repeat: no-repeat; background-position:center top;background-size:cover;border-radius: 0;">
                <div class="viwec-flex viwec-layout-inner" data-type="{%=type%}">
                    {% for (let i = 0; i < colsQty; i++) {
                    let width = 100 / colsQty + '%'; %}
                    <div class="viwec-column viwec-column-placeholder" style="width:{%=width%};">
                        <div class="viwec-column-sortable">
                        </div>

                        <div class="viwec-column-control">
                            <span class="viwec-column-paste" title="<?php esc_html_e( 'Paste', 'viwec-email-template-customizer' ); ?>">
                                <i class="dashicons dashicons-arrow-down-alt"></i>
                            </span>
                            <span class="viwec-column-edit" title="<?php esc_html_e( 'Edit column', 'viwec-email-template-customizer' ); ?>">
                                <i class="dashicons dashicons-edit"></i>
                            </span>
                        </div>

                    </div>
                    {% } %}
                </div>
            </div>
        </div>
    </script>

    <script id="viwec-input-textinput" type="text/html">
        <div>
            <input name="{%=key%}" type="text" class="form-control" title="{% if(typeof title == 'string'){ %} {%=title%} {% } %}"
                   autocomplete="off"/>
            {% if(typeof shortcodeTool !=='undefined' && shortcodeTool){ %}
            <span class="viwec-quick-shortcode"><i class="dashicons dashicons-menu"></i></span>
            <ul class="viwec-quick-shortcode-list"></ul>
            {% } %}
        </div>
    </script>

    <script id="viwec-input-texteditorinput" type="text/html">
        <div>
            <textarea name="{%=key%}" class="form-control" id="viwec-text-editor"/>
        </div>
    </script>

    <script id="viwec-input-radioinput" type="text/html">
        <div>
            {% for ( var i = 0; i < options.length; i++ ) { %}
            <label class="custom-control custom-radio  {% if (typeof inline !== 'undefined' && inline == true) { %}custom-control-inline{% } %}"
                   title="{%=options[i].title%}">
                <input name="{%=key%}" class="custom-control-input" type="radio" value="{%=options[i].value%}"
                       id="{%=key%}{%=i%}" {%if (options[i].checked) { %}checked="{%=options[i].checked%}" {% } %}>
                <label class="custom-control-label" for="{%=key%}{%=i%}">{%=options[i].text%}</label>
            </label>
            {% } %}
        </div>
    </script>

    <script id="viwec-input-radiobuttoninput" type="text/html">
        <div class="btn-group btn-group-toggle  {%if (extraClass) { %}{%=extraClass%}{% } %} clearfix" data-toggle="buttons">
            {% for ( var i = 0; i < options.length; i++ ) { %}
            <label class="{%if (options[i].checked) { %}active{% } %}  {%if (options[i].extraClass) { %}{%=options[i].extraClass%}{% } %}"
                   for="{%=key%}{%=i%} " title="{%=options[i].title%}">
                <input name="{%=key%}{%if (extraClass) { %}{%='-'+ extraClass%}{% } %}"
                       class="custom-control-input" type="radio" value="{%=options[i].value%}"
                       {%if (options[i].checked) { %}checked="{%=options[i].checked%}" {% } %}>
                {%if (options[i].icon) { %}<i class="{%=options[i].icon%}"></i>{% } %}
                {%=options[i].text%}
            </label>
            {% } %}
        </div>
    </script>

    <script id="viwec-input-header" type="text/html">
        <h6 class="header">{%=header%}</h6>
    </script>


    <script id="viwec-input-select" type="text/html">
        <div>
            <select class="form-control {% if(typeof classes !='undefined'){ %} {%=classes%} {% } %}">
                {% if(typeof options !=='undefined'){ %}
                {% for ( var i = 0; i < options.length; i++ ) { %}
                <option value="{%=options[i].id%}">{%=options[i].text%}</option>
                {% }} %}
            </select>
        </div>
    </script>

    <script id="viwec-input-select-group" type="text/html">
        <div>
            <select class="form-control {% if(typeof classes !='undefined'){ %} {%=classes%} {% } %}">
                {% for(var i in options){ %}
                {% if (Array.isArray(options[i])){ %}
                <optgroup label="{%=i.charAt(0).toUpperCase() + i.slice(1)%}">
                    {% for(let j of options[i]){ %}
                    <option value="{%=j.id%}">{%=j.text%}</option>
                    {% } %}
                </optgroup>
                {% }else{ %}
                <option value="{%=options[i].id%}">{%=options[i].text%}</option>
                {% }} %}
            </select>
        </div>
    </script>

    <script id="viwec-input-select2" type="text/html">
        {% let multipleCheck = typeof multiple !=='undefined' && multiple === true ? 'multiple' : ''; %}
        <div>
            <select {%=multipleCheck%} class="form-control {% if(typeof classes !='undefined'){ %} {%=classes%} {% } %}">
                {% if(typeof options !=='undefined'){ %}
                {% for ( var i = 0; i < options.length; i++ ) { %}
                <option value="{%=options[i].id%}">{%=options[i].text%}</option>
                {% }} %}
            </select>
        </div>
    </script>


    <script id="viwec-input-imageinput" type="text/html">
        <div>
            <input name="{%=key%}" type="text" class="form-control"/>
            <input name="file" type="file" class="form-control"/>
        </div>
    </script>

    <script id="viwec-input-colorinput" type="text/html">
        <div>
            <input name="{%=key%}" type="text" autocomplete="off" {% if (typeof value !== 'undefined' && value != false) { %}
            value="{%=value%}" {% } %} pattern="#[a-f0-9]{6}" class="form-control viwec-color-picker"/>
            <span class="viwec-clear dashicons dashicons-no-alt" title="<?php esc_html_e( 'Clear', 'viwec-email-template-customizer' ); ?>"></span>
        </div>
    </script>

    <script id="viwec-input-numberinput" type="text/html">
        <div>
            <input name="{%=key%}" type="number"
                   {% if (typeof value !== 'undefined' && value != false) { %} value="{%=value%}" {% } %}
            {% if (typeof min !== 'undefined' && min != false) { %}min="{%=min%}"{% }else{ %} min="0"{%} %}
            {% if (typeof max !== 'undefined' && max != false) { %}max="{%=max%}"{% } %}
            {% if (typeof step !== 'undefined' && step != false) { %}step="{%=step%}"{% } %}
            class="form-control"/>
        </div>
    </script>

    <script id="viwec-input-dateinput" type="text/html">
        <div>
            <input name="{%=key%}" type="date" {% if (typeof value !== 'undefined' && value != false) { %}
            value="{%=value%}" {% } %}
            {% if (typeof min !== 'undefined' && min != false) { %}min="{%=min%}"{% }else{ %} min="0"{%} %}
            {% if (typeof max !== 'undefined' && max != false) { %}max="{%=max%}"{% } %}
            {% if (typeof step !== 'undefined' && step != false) { %}step="{%=step%}"{% } %}
            class="form-control"/>
        </div>
    </script>

    <script id="viwec-input-checkboxinput" type="text/html">
        <div>
            <input name="{%=key%}" type="checkbox" value="1" class="form-control"/>
        </div>
    </script>

    <script id="viwec-input-bgimginput" type="text/html">
        <div>
            <button type="button" name="{%=key%}" class="{%=classes%} vi-ui button mini viwec-ctrl-btn">
                {%=text%}
            </button>
            <span class="viwec-clear dashicons dashicons-no-alt" title="<?php esc_html_e( 'Clear', 'viwec-email-template-customizer' ); ?>"></span>
        </div>
    </script>

    <script id="viwec-input-button" type="text/html">
        <div>
            <button class="vi-ui button mini viwec-ctrl-btn {% if(typeof classes !== 'undefined'){ %} {%=classes%} {% } %}" type="button">
                <i class="la  {% if (typeof icon !== 'undefined') { %} {%=icon%} {% } else { %} la-plus {% } %} la-lg"></i>
                {%=text%}
            </button>
        </div>
    </script>

    <script id="viwec-input-sectioninput" type="text/html">
        <div class="section">
            <div class="title active">
                <i class="dropdown icon"></i>
                {%=header%}
            </div>
            <div class="content active {%=key%}">

            </div>
        </div>
    </script>


    <script id="viwec-property" type="text/html">
        {% let formatCol = typeof col !== 'undefined' && col != false ? 'viwec-col-' + col + ' viwec-inline-block' : ''; %}
        {% let className = typeof classes !== 'undefined' ? classes : ''; %}
        {% if (typeof groupName !== 'undefined' && groupName != false) { %}
        <label class="viwec-group-name" for="input-model">{%=groupName%}</label>
        {% } %}
        <div class="{%=formatCol%} {%=className%}"
             data-key="{%=key%}" {% if (typeof group !=='undefined' && group !=null) { %}data-group="{%=group%}" {% } %}>
            <div class="viwec-form-group">
                {% if (typeof name !== 'undefined' && name != false) { %}
                <label class="viwec-control-label" for="input-model">{%=name%}</label>
                {% } %}
                <div class="input">
                </div>
            </div>
        </div>
    </script>

    <!-------------Order detail template------------------->
    <script id="order-detail-template-1" type="text/html">
        <table class="viwec-order-detail viwec-item-style-1" width="100%" align="center"
               style="text-align: center;border-collapse:collapse;line-height: 22px" border="0"
               cellpadding="0"
               cellspacing="0">
            <tr>
                <th class="viwec-text-product " style=" border:1px solid #dddddd; text-align: left; padding: 10px">Product</th>
                <th class="viwec-text-quantity " style=" border:1px solid #dddddd; text-align: center; padding: 10px">Quantity</th>
                <th class="viwec-text-price " style=" border:1px solid #dddddd; text-align: right;padding: 10px;width: 30%">Price</th>
            </tr>
			<?php
			for ( $i = 0; $i < 2; $i ++ ) {
				?>
                <tr>
                    <td class="viwec-p-name" style=" border:1px solid #dddddd; text-align: left; padding: 10px">Sample product</td>
                    <td class="" style=" border:1px solid #dddddd; text-align: center; padding: 10px">1</td>
                    <td class="viwec-p-price" style=" border:1px solid #dddddd; text-align: right;padding: 10px;">
                        <?php echo wc_price( 25 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
    </script>

    <script id="order-detail-template-2" type="text/html">
        <div class="viwec-order-detail">
			<?php
			for ( $i = 0; $i < 2; $i ++ ) {
				?>
                <table class="viwec-item-row" width="100%" height="auto" border="0" cellpadding="0" cellspacing="0"
                       style="border-collapse:separate;border-style:solid;">
                    <tr>
                        <td class="viwec-product-img" style="width: 150px;border-collapse:collapse;" border="0">
                            <img src="<?php echo esc_url( VIWEC_IMAGES . 'product.png' ) ?>" style="width: 100%">
                        </td>
                        <td class="" style="vertical-align: middle; padding-left: 15px;padding-top: 5px;">
                            <p class="viwec-product-name">Product name</p>
                            <p class="viwec-product-quantity"><span class="viwec-text-quantity">x</span> 1</p>
                            <p class="viwec-product-price"><?php echo wc_price( 25 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></p>
                        </td>
                    </tr>
                </table>
				<?php
				if ( $i === 0 ) {
					?>
                    <div class="viwec-product-distance" style="padding-top: 10px;"></div>
					<?php
				}
			}
			?>
        </div>
    </script>

    <script id="order-detail-template-3" type="text/html">
        <div class="viwec-order-detail">
			<?php
			for ( $i = 0; $i < 2; $i ++ ) {
				?>
                <table class="viwec-item-row template-3" width="100%" height="auto" border="0" cellpadding="0" cellspacing="0"
                       style="border-collapse:collapse;border-style:solid; ">
                    <tr>
                        <td class="viwec-product-img" style="width: 150px;border-collapse:collapse;" border="0">
                            <img src="<?php echo esc_url( VIWEC_IMAGES . 'product.png' ); ?>" style="width: 100%;vertical-align: middle;">
                        </td>
                        <td valign="middle" style="padding:0 15px">
                            <p class="viwec-product-name">Sample product</p>
                            <p class="viwec-product-quantity"><span class="viwec-text-quantity">x</span> 1</p>
                        </td>
                        <td style="text-align: right;">
                            <p style="white-space: nowrap;" class="viwec-product-price">
								<?php echo wc_price( 25 );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </p>
                        </td>
                    </tr>
                </table>
				<?php
				if ( $i === 0 ) {
					?>
                    <div class="viwec-product-distance" style="padding-top: 10px;"></div>
					<?php
				}
			} ?>
        </div>
    </script>

    <!-------------Order subtotal template------------------->
    <script id="order-subtotal-template" type="text/html">
        <table class="viwec-order-subtotal" width="100%" align="center" style="text-align: center;border-collapse: collapse" border="0"
               cellpadding="0" cellspacing="0">
			<?php
			$data = [
				'viwec-text-subtotal'    => [ 'name' => 'Subtotal', 'value' => wc_price( 50 ) ],
				'viwec-text-discount'    => [ 'name' => 'Discount', 'value' => '-' . wc_price( 5 ) ],
				'viwec-text-shipping'    => [ 'name' => 'Shipping', 'value' => wc_price( 10 ) ],
				'viwec-text-refund-full' => [ 'name' => 'Order fully refunded', 'value' => '-' . wc_price( 0 ) ],
				'viwec-text-refund-part' => [ 'name' => 'Refund', 'value' => '-' . wc_price( 0 ) ],
			];
			foreach ( $data as $class => $content ) {
				?>
                <tr>
                    <td class="<?php echo esc_attr( $class ) ?> viwec-td-left viwec-order-subtotal-style" style="text-align: left; border-style: solid; border-width: 0;">
						<?php echo esc_html( $content['name'] ) ?>
                    </td>
                    <td class="viwec-td-right viwec-order-subtotal-style" style="text-align: right;border-style: solid; border-width: 0; width: 30%;">
						<?php echo wp_kses( $content['value'], viwec_allowed_html() ) ?>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
    </script>

    <!-------------Order total template------------------->
    <script id="order-total-template" type="text/html">
        <table class="viwec-order-total" width="100%" align="center" style="text-align: center;border-collapse: collapse" border="0" cellpadding="0"
               cellspacing="0">
            <tr>
                <td class=" viwec-td-left viwec-order-total-style" style="text-align: left; border-style: solid; border-width: 0; ;">
                    <div class="viwec-text-total">Total</div>
                </td>
                <td class="viwec-td-right viwec-order-total-style" style="text-align: right;border-style: solid; border-width: 0; width: 30%;">
					<?php echo wc_price( 55 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </td>
            </tr>
        </table>
    </script>


    <!-------------Order note template------------------->
    <script id="order-note" type="text/html">
        <table class="order-payment-method" width="100%" align="center" style="text-align: center;border-collapse: collapse" border="0"
               cellpadding="0" cellspacing="0">
            <tr>
                <td class=" viwec-td-left viwec-note-style" style="text-align: left; border-style: solid; border-width: 0; ;">
                    <div class="viwec-text-note">Note</div>
                </td>
                <td class="viwec-td-right viwec-note-style" style="text-align: right; border-style: solid; border-width: 0; width: 30%;">
                    Customer note
                </td>
            </tr>
        </table>
    </script>

    <!-------------Payment method template------------------->
    <script id="order-payment-method" type="text/html">
        <table class="order-payment-method" width="100%" align="center" style="text-align: center;border-collapse: collapse" border="0"
               cellpadding="0" cellspacing="0">
            <tr>
                <td class=" viwec-td-left viwec-payment-method-style" style="text-align: left; border-style: solid; border-width: 0; ;">
                    <div class="viwec-text-payment">Payment method</div>
                </td>
                <td class="viwec-td-right viwec-payment-method-style" style="text-align: right; border-style: solid; border-width: 0; width: 30%;">
                    Paypal
                </td>
            </tr>
        </table>
    </script>

    <!-------------Shipping method template------------------->
    <script id="order-shipping-method" type="text/html">
        <table class="order-shipping-method" width="100%" align="center" style="text-align: center;border-collapse: collapse" border="0"
               cellpadding="0" cellspacing="0">
            <tr>
                <td class=" viwec-td-left viwec-shipping-method-style" style="text-align: left; border-style: solid; border-width: 0; ;">
                    <div class="viwec-text-shipping">Shipping method</div>
                </td>
                <td class="viwec-td-right viwec-shipping-method-style" style="text-align: right; border-style: solid; border-width: 0; width: 30%;">
                    Flat rate
                </td>
            </tr>
        </table>
    </script>

    <script id="viwec-recover-email-content" type="text/html">
        <p><?php esc_html_e( 'Hi John, Just to let you know — we\'ve received your order #859, and it is now being processed:', 'viwec-email-customizer' ); ?></p>
        <h2 style="margin: 0; padding: 20px 0; font-size: 18px">
			<?php
			esc_html_e( '[Order #123]', 'viwec-email-customizer' );
			printf( "(%s)", esc_html( date_i18n( wc_date_format(), current_time( 'U' ) ) ) );
			?>
        </h2>

        <div>
            <table class="viwec-sample-table" align="center" width="100%" border="0" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th class="head td"><?php esc_html_e( 'Product', 'viwec-email-customizer' ); ?></th>
                    <th class="head td"><?php esc_html_e( 'Quantity', 'viwec-email-customizer' ); ?></th>
                    <th class="head td"><?php esc_html_e( 'Price', 'viwec-email-customizer' ); ?></th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td class="body td">
                        <img style="width: 50px;display: none;" src="<?php echo esc_url( VIWEC_IMAGES . 'product.png' ) ?>">
						<?php esc_html_e( 'Product name', 'viwec-email-customizer' ); ?></td>
                    <td class="body td">1</td>
                    <td class="body td">
						<?php
						echo wc_price( 20 );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
                    </td>
                </tr>
                </tbody>

                <tfoot>
                <tr>
                    <th class="body td" colspan="2"><?php esc_html_e( 'Subtotal:', 'viwec-email-customizer' ); ?></th>
                    <td class="body td">
						<?php
						echo wc_price( 20 );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
                    </td>
                </tr>
                <tr>
                    <th class="body td" colspan="2"><?php esc_html_e( 'Shipping:', 'viwec-email-customizer' ); ?></th>
                    <td class="body td"><?php esc_html_e( 'Free shipping', 'viwec-email-customizer' ); ?></td>
                </tr>
                <tr>
                    <th class="body td" colspan="2"><?php esc_html_e( 'Payment method:', 'viwec-email-customizer' ); ?></th>
                    <td class="body td"><?php esc_html_e( 'PayPal', 'viwec-email-customizer' ); ?></td>
                </tr>
                <tr>
                    <th class="body td" colspan="2"><?php esc_html_e( 'Total:', 'viwec-email-customizer' ); ?></th>
                    <td class="body td">
						<?php
						echo wc_price( 20 );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <table class="viwec-sample-address" align="center" width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top">
                        <h2 style="margin: 0; padding: 20px 0; font-size: 18px">
							<?php esc_html_e( 'Billing address', 'viwec-email-customizer' ); ?>
                        </h2>
                        <div>
                            <p>John Doe</p>
                            <p>Ap #867-859 Sit Rd.</p>
                            <p>Azusa, NY 10001</p>
                            <p>United States (US)</p>
                            <p>0123456789</p>
                            <p>johndoe@domain.com</p>
                        </div>
                    </td>
                    <td valign="top">
                        <h2 style="margin: 0; padding: 20px 0; font-size: 18px">
							<?php esc_html_e( 'Shipping address', 'viwec-email-customizer' ); ?>
                        </h2>
                        <div>
                            <p>John Doe</p>
                            <p>Ap #867-859 Sit Rd.</p>
                            <p>Azusa, NY 10001</p>
                            <p>United States (US)</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </script>

    <script id="viwec-recover-email-heading" type="text/html">
        <div>
			<?php esc_html_e( 'Thank you for your order', 'viwec-email-customizer' ); ?>
            <span class="viwec-note"><?php esc_html_e( 'The heading of original email will be transferred here', 'viwec-email-customizer' ); ?></span>
        </div>
    </script>

    <script id="viwec-wc-hook" type="text/html">
        <div>
            <h2 style="margin: 0; padding: 10px 0; font-size: 18px">
				<?php esc_html_e( 'Other plugin information', 'viwec-email-customizer' ); ?>
                <span class="viwec-hook-name">(woocommerce_email_before_order_table)</span>
            </h2>
            <table class="viwec-sample-table" align="center" width="100%" border="0" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th class="head td">ID</th>
                    <th class="head td">Items</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="body td">1</td>
                    <td class="body td">Item</td>
                </tr>
                <tr>
                    <td class="body td">2</td>
                    <td class="body td">Item</td>
                </tr>
                </tbody>
            </table>
        </div>
    </script>
</div>
