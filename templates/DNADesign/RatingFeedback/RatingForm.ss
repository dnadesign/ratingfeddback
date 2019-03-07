<% if $IncludeFormTag %>
<form $AttributesHTML>
<% end_if %>
	<% if $Message %>
	<p id="{$FormName}_error" class="message $MessageType">$Message</p>
	<% else %>
	<p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
	<% end_if %>

	<fieldset>
		<legend class="rating-legend">
			<h4 class="rating-title">$Title</h4>
			<% if $Intro %>
				<span class="rating-intro <% if $Submitted %>rating-intro--success<% end_if %>">$Intro</span>
			<% end_if %>
		</legend>		

		<% if $IncludeRating %>
			<div class="rating-stars-wrapper">
				$Fields.fieldByName('Rating').FieldHolder
			</div>
		<% end_if %>

		<% if not $Submitted %>
			<% if $IncludeFeedback %>
			<div class="rating-comment-wrapper">				
				<div class="rating-comment <% if $HideFeedback %>rating-comment--hidden<% end_if %>">
					$Fields.fieldByName('Comments').FieldHolder
				</div>				
			</div>
			<% end_if %>
			
			<div class="rating-action-wrapper">
				<% if $Fields.fieldByName('Captcha') %>
					<div class="rating-captcha">
						$Fields.fieldByName('Captcha').FieldHolder
					</div>
				<% end_if %>

				$HiddenFields

				<% if $Actions %>
					<div class="Actions">
						<% loop $Actions %>
							$Field
						<% end_loop %>
					</div>
				<% end_if %>
			</div>

		<% else %>
			<% if $SubmittedComments %>
				<div class="rating-comment rating-comment--submitted">
					<p>$SubmittedComments</p>
				</div>
			<% end_if %>
		<% end_if %>

		<div class="clear"><!-- --></div>
	</fieldset>
	
<% if $IncludeFormTag %>
</form>
<% end_if %>
