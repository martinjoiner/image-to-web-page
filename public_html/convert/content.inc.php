
<h1>Convert an image file to a HTML web page</h1>


<!-- TODO: This form will AJAX post to POST/generate -->
<form class="generateInputs" action="/POST/generate/" method="POST" enctype="multipart/form-data">
	
	<div class="row">
		<label for="wording">Image file</label>
		<input type="file" name="imagefile" required>
	</div>

	<div class="row">
		<label for="wording">Wording</label>
		<textarea class="wordingInput" type="text" name="wording" id="wording" required>Over-exposure to images of unobtainable beauty may be warping our perception of reality. </textarea>
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
		<label for="image_width">Image width</label> 
		<input type="number" name="image_width" id="image_width" value="600" min="100" step="10" max="800">
	</div>

	<div class="row">
		<label for="pixelwidth">Pixel width</label> 
		<input type="number" name="pixelwidth" id="pixelwidth" value="10" min="1" max="50">
	</div>

	<div class="row">
		<label>&nbsp;</label>
		<input type="submit" value="Convert">
	</div>

</form>
