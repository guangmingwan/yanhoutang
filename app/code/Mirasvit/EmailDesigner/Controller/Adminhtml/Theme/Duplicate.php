<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email-designer
 * @version   1.1.42
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;

use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;
use Mirasvit\Email\Api\Service\CloneServiceInterface;

class Duplicate extends Theme
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $id    = $this->getRequest()->getParam(ThemeInterface::ID);
            $model = $this->themeRepository->get($this->getRequest()->getParam(ThemeInterface::ID));

            try {
                $this->duplicate($model);
                $this->messageManager->addSuccessMessage(__('Theme "%1" was successfully duplicated.', $model->getTitle()));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find theme to duplicate'));

        return $resultRedirect->setPath('*/*/');
    }

    private function duplicate(ThemeInterface $theme)
    {
        /** @var CloneServiceInterface $clonner */
        $clonner = $this->_objectManager->get(CloneServiceInterface::class);

        $themeClone = $clonner->duplicate($theme, $this->themeRepository, [
            ThemeInterface::ID,
            ThemeInterface::CREATED_AT,
            ThemeInterface::UPDATED_AT
        ], [ThemeInterface::TITLE => $theme->getTitle().' copy']);

        return $themeClone;
    }
}
