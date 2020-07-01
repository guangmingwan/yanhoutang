<?php

namespace Potato\Pdf\Block\Adminhtml\Template\Edit;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;

/**
 * Class LoadButton
 */
class LoadButton extends Field
{
    public function toHtml()
    {
        $ajaxUrl = $this->getUrl("adminhtml/email_template/defaultTemplate");
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'load_template_button',
                'label' => __('Load Template')
            ]
        );
        $html = $button->toHtml();

        $html .= '<script type="text/javascript">
            require([
            "jquery"
            ], function($){                
                var poLoadTemplate = function()
                {
                    $.ajax({
                        url: "' .$ajaxUrl . '",
                        data: {code: $(\'[name="code"]\').val()},
                        success: function(json) {
                            if (json && json.template_text) {
                                $("#content").val(json.template_text);
								$("#content").change();                           
                            }
                            window.poPdfInitTemplateEditor();
                            $(\'.po-pdf-content-editor-header__edit\').first().click();
                        },
                        context: $("body"),
                        showLoader: true
                    });
                };
                
                $("#load_template_button").click(function(){
                    poLoadTemplate();
                });
            });
           </script>';

        return $html;
    }
}
