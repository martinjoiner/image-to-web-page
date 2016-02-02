
<h1>Convert an image file to a HTML web page</h1>


<!-- TODO: This form will AJAX post to POST/generate -->
<form class="generateInputs" action="/POST/generate/" method="POST" enctype="multipart/form-data">
	
	<div class="row">
		<label for="wording">Image file</label>
		<input type="file" name="imagefile">
	</div>
	<div class="row">

		<label for="wording">Wording</label>
		<input type="text" name="wording" id="wording" value="Over-exposure to images of unobtainable beauty may be warping our perception of reality. ">
	</div>
	<div class="row">

		<label for="posterise">Posterise</label> 
		<select name="posterise" id="posterise">
			<option value="0">Do not posterise</option>
		    <option value="1">1</option>
		    <option value="2">2</option>
		    <option value="3">3</option>
		    <option value="4">4</option>
		    <option value="5">5</option>
		    <option value="6">6</option>
		</select>

	</div>
	<div class="row">

		<label for="desiredwindowwidth">Window width</label> 
		<input type="number" name="desiredwindowwidth" id="desiredwindowwidth" value="1200">

	</div>
	<div class="row">

		<label for="pixelwidth">Pixel width</label> 
		<input type="number" name="pixelwidth" id="pixelwidth" value="10">

	</div>
	<div class="row">

		<label>&nbsp;</label>
		<input type="submit" value="Create">

</form>
