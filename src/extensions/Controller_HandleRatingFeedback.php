<?php

namespace DNADesign\RatingFeedback\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use SilverStripe\Security\Member;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DisabledTransformation;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Session;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use DNADesign\RatingFeedback\Models\RatingFeedback;

class Controller_HandleRatingFeedback extends Extension
{
    private static $allowed_actions = [
        'RatingFeedbackForm'
    ];

    public function onBeforeInit()
    {
        // Require the necessary javascript
        $useDefaultJS = Config::inst()->get(self::class, 'include_default_js');
        if ($useDefaultJS) {
            Requirements::javascript('dnadesign/ratingfeedback:client/js/ratingfeedback.js');
        }

        // Require necessary css
        $useDefaultCSS = Config::inst()->get(self::class, 'include_default_css');
        if ($useDefaultCSS) {
            Requirements::css('dnadesign/ratingfeedback:client/css/ratingfeedback.css');
        }
    }

    public function RatingFeedbackForm()
    {
        // Config
        $maxstars = ($this->owner->data()->getBlockMaxStars()) ? $this->owner->data()->getBlockMaxStars() : 5;
        $title = $this->owner->data()->getBlockTitle();

        // Flags
        $submitted = false;
        $submittedComments = null;

        $referrer = $this->owner->Link();

        // By default, record current page ID
        // And currentUserID if logged in
        $fields = new FieldList([
            HiddenField::create('PageID', '', $this->owner->ID),
            HiddenField::create('SubmittedByID', '', Member::currentUserID())
        ]);

        // Build Star Rating Field
        $stars = [];
        for ($i=1; $i<=$maxstars; $i++) {
            $stars[$i] = sprintf('%s star%s', $i, ($i > 1) ? 's' : '');
        }
        $options = OptionsetField::create('Rating', 'Rate this', $stars)->addExtraClass('field--starrating');
        $options->setTemplate('StarRatingField');

        // Include field if needed
        if ($this->owner->data()->includeRating()) {
            $fields->push($options);
        }

        // Build Comment Field
        $comments = TextareaField::create('Comments', 'Add a comment')->addExtraClass('field--comment');
        
        if ($this->owner->data()->includeFeedback()) {
            $fields->push($comments);

            if ($this->owner->data()->includeRequireFeedbackIfRatingLessThanAttribute()) {
                $comments->setAttribute('data-require-if-less-than', $this->owner->data()->RequireCommentIfRatingLessThan);
            }
        }

        // Config Form
        $action = new FormAction('recordRating', 'Submit');
        $actions = new FieldList($action);
        $required = new RequiredFields('Rating');

        if ($this->owner->data()->isFeedbackRequired()) {
            $required->addRequiredField('Comments');
        }

        $form = new Form($this->owner, __FUNCTION__, $fields, $actions, $required);

        $form->addExtraClass('ratingfeedback-form');
        $form->setAttribute('data-rating-type', $this->owner->data()->getRatingType());

        $form->disableSecurityToken();
        $form->setFormMethod('POST');

        // If form is submitted
        $session = $this->owner->getRequest()->getSession();
        if ($rating = $session->get('RatingBlock'. $this->owner->ID)) {
            if ((int) $rating->PageID === $this->owner->ID) {
                $submitted = true;
                $form->addExtraClass('submitted');

                $form->loadDataFrom($rating)
                    ->addExtraClass('disabled')
                    ->transform(new DisabledTransformation());
            
                $options->performDisabledTransformation(true);

                $submittedComments = trim($rating->Comments);
            }
        }

        $form->setRedirectToFormOnValidationError(true);

        // Enable Spam Protection
        if ($this->owner->data()->enableSpamProtection()) {
            $form->enableSpamProtection();
        }

        $data = new ArrayData(array(
            'anchor' => 'rating'.$this->owner->ID,
            'Submitted' => $submitted,
            'SubmittedComments' => $submittedComments,
            'Title' => ($submitted) ? '' : $title,
            'Intro' => ($submitted) ? $this->owner->data()->getBlockSuccess() : $this->owner->data()->getBlockIntro(),
            'IncludeRating' => $this->owner->data()->includeRating(),
            'IncludeFeedback' => $this->owner->data()->includeFeedback(),
            'HideFeedback' => $this->owner->data()->HideCommentField
        ));

        return $form
            ->customise($data)
            ->setTemplate('DNADesign\\RatingFeedback\\Form\\RatingForm')
            ->setHTMLID($this->owner->data()->getHTMLID());
    }

    /**
     * Rating block submission action
     */
    public function recordRating($data, $form)
    {
        $rating = new RatingFeedback();
        $form->saveInto($rating);
        $rating->write();

        
        $session = $this->owner->getRequest()->getSession();
        $session->set('RatingBlock'. $this->owner->ID, $rating);
        
        // Redirect
        $url = Controller::join_links($this->owner->Link(), '#'.$this->owner->data()->getHTMLID());

        return $this->owner->redirect($url);
    }
}
