<?php

class Local_Manadev_DownloadableController extends Mage_Adminhtml_Controller_Action {
	public function changeStatusAction() {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');

        $this->getResource()->changeStatus($id, $status);

        $response = array('success' => true);

        $this->getResponse()->setBody(json_encode($response));

    }

    /**
     * @return Local_Manadev_Resource_Download_Status
     */
    public function getResource() {
        return Mage::getResourceSingleton('local_manadev/download_status');
    }
}