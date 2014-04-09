<?php
/*

PHPGraphLib Graphing Library

The first version PHPGraphLib was written in 2007 by Elliott Brueggeman to
deliver PHP generated graphs quickly and easily. It has grown in both features
and maturity since its inception, but remains PHP 4.04+ compatible. Originally
available only for paid commerial use, PHPGraphLib was open-sourced in 2013 
under the MIT License. Please visit http://www.ebrueggeman.com/phpgraphlib 
for more information.

---

The MIT License (MIT)

Copyright (c) 2013 Elliott Brueggeman

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
include('../phpgraphlib_pie.php');

class PHPGraphLib2dPie extends PHPGraphLibPie
{
	const PIE_3D_HEIGHT_PERCENT = 4;
	const PIE_LEGEND_TEXT_WIDTH = 6;
	const PIE_LEGEND_TEXT_HEIGHT = 12;
	const PIE_LABEL_TEXT_WIDTH = 6;
	const PIE_LABEL_TEXT_HEIGHT = 12;
	const PIE_LEGEND_PADDING = 5; 
	const PIE_CENTER_Y_OFFSET = 50; //in %
	const PIE_CENTER_X_OFFSET = 50; //in %
	const PIE_CENTER_LEGEND_SCALE = 75; //offset in % of existing coords when legend
	const PIE_WIDTH_PERCENT = 75; //default width % of total width
	const PIE_HEIGHT_PERCENT = 100; 
	const PIE_LABEL_SCALE = 90; //in % scale width/height if data labels
	const PIE_LEGEND_SCALE = 60;//in % scale width/height if legend

	//internals - do not change
	protected $pie_width;
	protected $pie_height;
	protected $pie_center_x;
	protected $pie_center_y;
	protected $pie_legend_x;
	protected $pie_legend_y;
	protected $pie_data_label_space;
	protected $pie_3D_height;

	protected $pie_data_max_length = 0;
	protected $pie_color_pointer = 0;
	protected $pie_data_array_percents = array();
	protected $bool_x_axis = false;
	protected $bool_y_axis = false;
	protected $bool_data_points = false;
	protected $pie_precision = 0; //number of significant digits in label %
	protected $bool_legend = true;
	protected $bool_data_labels = true;

	//default colors, in order of display on graph
	protected $pie_avail_colors = array(
		'pastel_orange_1', 'pastel_orange_2', 'pastel_blue_1', 'pastel_green_1',
		'clay', 'pastel_blue_2', 'pastel_yellow', 'silver', 'pastel_green_2',
		'brown', 'gray', 'pastel_purple', 'olive', 'aqua', 'yellow', 'teal', 'lime'
	);

	protected function calcCoords() 
	{
		parent::calcCoords();
		
		//calc coords of pie center and width/height
		$this->pie_width = min($this->width,$this->height);
		$this->pie_height = $this->pie_width * (self::PIE_HEIGHT_PERCENT / 100);
		
		
 		$this->pie_center_y = $this->height * (self::PIE_CENTER_Y_OFFSET / 100);
 		$this->pie_center_x = $this->width * (self::PIE_CENTER_X_OFFSET / 100);
 
		//set data label spacing 
		if ($this->bool_data_labels) {
			//set to number of pixels that are equal to text width
			//7 is a base spacer that all labels get
			$this->pie_data_label_space = 7 + $this->width / 75;
			$this->pie_width -= $this->pie_data_label_space;
			$this->pie_height *= self::PIE_LABEL_SCALE / 100;
		}

 		if ($this->bool_legend) {
// 			//compensate for legend with lesser preset percent
 			$this->pie_width *= self::PIE_LEGEND_SCALE / 100;
 			$this->pie_height *= self::PIE_LEGEND_SCALE / 100;
 			$this->pie_center_x *= self::PIE_CENTER_LEGEND_SCALE / 100;
 		}
// 		$this->pie_3D_height = self::PIE_3D_HEIGHT_PERCENT * ($this->pie_width / 100);
// 		$this->pie_height = $this->pie_width * (self::PIE_HEIGHT_PERCENT / 100);
	}

	protected function generateBars()
	{
		$this->resetColorPointer();
		
		$radius = min($this->pie_width,$this->pie_height);
		
		$arcStart = 0;	
		foreach ($this->pie_data_array_percents as $key => $value) {
			$color = $this->generateNextColor();
			// do not draw if the value is zero
			if (! $value == 0){
				imagefilledarc($this->image, $this->pie_center_x, $this->pie_center_y, $radius, $radius, $arcStart, (360*$value)+$arcStart, $color, IMG_ARC_PIE);
				$arcStart += 360 * $value;
			}
			if ($this->bool_data_labels) { 
				$this->generateDataLabel($value, $arcStart); 
			}
		}
	}

	protected function generateDataLabel($value, $arcStart) 
	{
		//midway if the mid arc angle of the wedge we just drew
		$midway = ($arcStart - (180 * $value));

		$pi = atan(1.0) * 4.0;
		$theta = ($midway / 180) * $pi;
		$valueX = $this->pie_center_x + ($this->pie_width / 2 + $this->pie_data_label_space) * cos($theta);
		$valueY = $this->pie_center_y + ($this->pie_width / 2 + $this->pie_data_label_space) * sin($theta);
		$displayValue = $this->formatPercent($value);
		$valueArray = $this->dataLabelHandicap($valueX, $valueY, $displayValue, $midway);
		$valueX = $valueArray[0];
		$valueY = $valueArray[1];	
		imagestring($this->image, 2, $valueX, $valueY, $displayValue, $this->label_text_color);
	}


}
?>