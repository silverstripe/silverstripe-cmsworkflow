<?php
/**
 * Report showing removal requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class UnapprovedDeletions2StepReport extends SS_Report
{
    public function title()
    {
        return _t('UnapprovedDeletions2StepReport.TITLE', "Deletion requests I need to approve");
    }
    public function sourceRecords($params, $sort, $limit)
    {
        increase_time_limit_to(120);
        
        $res = WorkflowTwoStepRequest::get_by_publisher(
            'WorkflowDeletionRequest',
            Member::currentUser(),
            array('AwaitingApproval')
        );

        $doSet = new DataObjectSet();
        if ($res) {
            foreach ($res as $result) {
                if (!$result->canApprove()) {
                    continue;
                }
                if ($wf = $result->openWorkflowRequest()) {
                    $result->WFAuthorTitle = $wf->Author()->Title;
                    $result->WFAuthorID = $wf->AuthorID;
                    $result->WFRequestedWhen = $wf->Created;
                    $result->WFApproverID = $wf->ApproverID;
                    $result->WFPublisherID = $wf->PublisherID;
                    $result->BacklinkCount = $result->BackLinkTracking()->Count();
                    $doSet->push($result);
                }
            }
        }
        
        if ($sort) {
            $parts = explode(' ', $sort);
            $field = $parts[0];
            $direction = $parts[1];
            
            if ($field == 'AbsoluteLink') {
                $sort = 'URLSegment ' . $direction;
            }
            if ($field == 'Subsite.Title') {
                $sort = 'SubsiteID ' . $direction;
            }
            
            $doSet->sort($sort);
        }
        
        if ($limit && $limit['limit']) {
            return $doSet->getRange($limit['start'], $limit['limit']);
        } else {
            return $doSet;
        }
    }
    public function columns()
    {
        return array(
            "Title" => array(
                "title" => "Page name",
                'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
            ),
            "WFAuthorTitle" => array(
                "title" => "Requested by",
            ),
            "WFRequestedWhen" => array(
                "title" => "Requested",
                'casting' => 'SS_Datetime->Full',
            ),
            'AbsoluteLink' => array(
                'title' => 'URL',
                'formatting' => '$value " . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a target=\"_blank\" href=\"$value?stage=Stage\">(draft)</a>'
            ),
            "BacklinkCount" => array(
                "title" => "Incoming links",
                'formatting' => '".($value ? "<a href=\"admin/show/$ID#Root_Expiry\" title=\"View backlinks\">yes, $value</a>" : "none") . "'
            ),
        );
    }

    
    /**
     * This alternative columns method is picked up by SideReportWrapper
     */
    public function sideReportColumns()
    {
        return array(
            "Title" => array(
                "link" => true,
            ),
            "WFAuthorTitle" => array(
                "formatting" => 'Requested by $value',
            ),
            "WFRequestedWhen" => array(
                "formatting" => ' on $value',
                'casting' => 'SS_Datetime->Full'
            ),
        );
    }
    public function canView()
    {
        return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
    }
    
    public function group()
    {
        return _t('WorkflowRequest.WORKFLOW', 'Workflow');
    }
}
