<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages which require publication.
 * 
 * @package cmsworkflow
 */
class MyDeletionRequestsSideReport extends SideReport {
	function title() {
		return _t('DeletionRequestSideReport.TITLE',"Workflow: Awaiting deletion");
	}
	function records() {
		return WorkflowRequest::get_by_publisher(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
		);
	}
}

?>