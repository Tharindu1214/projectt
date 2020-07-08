<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<section class="section">
	<div class="sectionhead">
		<h4> <?php echo Labels::getLabel('LBL_Questions',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space togglewrap" >

<?php
if(!empty($questions)){
	foreach($questions as $question){
?>
<div class="repeatedrow">
	<h6><i class="ion-arrow-right-a icon"></i>&nbsp;<?php echo $question['question_title']; ?></h6>
	<div class="rowbody">
	<p>
	<?php
	$question_type = $question['question_type'];
	$answer = $question['qta_answers'];
	$unserializedAnswer = @unserialize(FatUtility::decodeHtmlEntities($question['qta_answers']));
	
	if( $question_type == Questions::TYPE_RATING_5 || $question_type == Questions::TYPE_RATING_10 ){
		$answer = FatUtility::int($answer);
		$rateOutOf = ($question_type == Questions::TYPE_RATING_5)? 5 :($question_type == Questions::TYPE_RATING_10 ? 10 :0);
	?>
	<ul class="rating list-inline">
	<?php for($j=1;$j<=$rateOutOf;$j++){ ?>	
		<li class="<?php echo $j<=round($answer)?"active":"in-active" ?>">
			<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
		<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($answer)?"#ff3a59":"#474747" ?>" /></g></svg>
		</li>
	<?php } ?>
	</ul>
	<?php
	} else {
		$displayAns = ($unserializedAnswer === false)? $answer : implode("\n" ,$unserializedAnswer);
		echo $displayAns ? nl2br($displayAns) : 'N/A';
	}
	?>
	</p>
	</div>    
</div>
<?php
	}
	// $postedData['page']=$page;
	echo FatUtility::createHiddenFormFromData ( $postedData, array (
			'name' => 'frmFeedbackQuestionSearchPaging'
	) );
	$pagingArr = array('pageCount' => $pageCount,'page' => $page,'recordCount' => $recordCount,'callBackJsFunc' => 'goToNextFeedbackQuestionPage','adminLangId'=>$adminLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
}
		?>
	</div>
</section>
