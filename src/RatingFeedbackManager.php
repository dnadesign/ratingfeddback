<?php

class RatingFeedbackManager extends ModelAdmin {
	
	private static $managed_models = [
		'RatingFeedback'
	];

	private static $menu_title = 'Rating Manager';

	private static $url_segment = 'rating';
	
	public function getEditForm($id = null, $fields = null) 
	{
		$form = parent::getEditForm($id , $fields);
		$field = $form->Fields()->fieldByName($this->modelClass);
		$config = $field->getConfig();

		$paginator = $config->getComponentByType('GridFieldPaginator');
		$paginator->setItemsPerPage(500);
		
		return $form;
	}

	public function getSearchContext() 
	{
        $context = parent::getSearchContext();
		
		$dateField = new DateField("q[FromDate]", "From Date");
		
		// Get the DateField portion of the DatetimeField and
        // Explicitly set the desired date format and show a date picker
        $dateField->setConfig('dateformat', 'dd/MM/yyyy')->setConfig('showcalendar', true);
		$context->getFields()->push($dateField);
		
		$dateField = new DateField("q[ToDate]", "To Date");
		
		// Get the DateField portion of the DatetimeField and
        // Explicitly set the desired date format and show a date picker
        $dateField->setConfig('dateformat', 'dd/MM/yyyy')->setConfig('showcalendar', true);
		$context->getFields()->push($dateField);
		
		return $context;
    }
	
	public function getList() 
	{
		$list = parent::getList();
		// Access search parameters
        $params = $this->request->requestVar('q'); 
		
		if(isset($params['FromDate']) && $params['FromDate']) {
            $list = $list->exclude('Created:LessThan', $params['FromDate']);
        }
		
		if(isset($params['ToDate']) && $params['ToDate']) {
            // split date into day month year variables
            list($day,$month,$year) = sscanf($params['ToDate'], "%d/%d/%d");
			
			// date functions expect US date format, create new date object
            $date = new Datetime("$month/$day/$year");
			
			// create interval of Plus 1 Day (P1D)
            $interval = new DateInterval('P1D');
			
			// add interval to the date
            $date->add($interval);
			
			// use the new date value as the GreaterThan exclusion filter
            $list = $list->filter('Created:LessThan', date_format($date, 'd/m/Y'));
        }
		
		return $list;
    }
}