<?php
require_once "FPDF_Extended.class.php";

class FPDF_ExtendedTables extends FPDF_Extended
{
	function WriteTable($data)
	{
		foreach ($data as $rowData)
		{
			$height = 0;
			$nb = 0;
			
			foreach ($rowData as $columnData)
			{
				$this->SetStyle($columnData);
				$nb = max($nb, $this->NbLines($columnData["width"], $columnData["text"]));
				$height = $columnData["height"];
			}
			$h = $height * $nb;
			
			$this->CheckPageBreak($h);
			
			foreach ($rowData as $column => $columnData)
			{
				$w = @$columnData["width"];
				if (!$w)
				{
					$w = $data[0][$column]["width"];
				}
				$a = $columnData["align"];
				
				$x = $this->GetX();
				$y = $this->GetY();
				
				$this->SetStyle($columnData);
				
				if (@$columnData["fillColor"])
				{
					$this->Rect($x, $y, $w, $h, "F");
				}
				
				// Draw Cell Border
				if (substr_count($columnData["lineArea"], "T") > 0)
				{
					$this->Line($x, $y, $x + $w, $y);
				}
				
				if (substr_count($columnData["lineArea"], "B") > 0)
				{
					$this->Line($x, $y + $h, $x + $w, $y + $h);
				}
				
				if (substr_count($columnData["lineArea"], "L") > 0)
				{
					$this->Line($x, $y, $x, $y + $h);
				}
				
				if (substr_count($columnData["lineArea"], "R") > 0)
				{
					$this->Line($x + $w, $y, $x + $w, $y + $h);
				}
				
				$this->MultiCell($w, $columnData["height"], $columnData["text"], 0, $a, 0);
				
				$this->SetXY($x + $w, $y);
			}
			
			// Go to the next line
			$this->Ln($h);
		}
	}
	
	function SetStyle($data)
	{
		$fontData = @$data["font"];
		if ($fontData)
		{
			$this->SetFont($fontData["name"], $fontData["style"], $fontData["size"]);
		}
		
		$fillColor = @$data["fillColor"];
		if ($fillColor)
		{
			$color = explode(",", $fillColor);
			$this->SetFillColor($color[0], $color[1], $color[2]);
		}
		
		$textColor = @$data["textColor"];
		if ($textColor)
		{
			$color = explode(",", $textColor);
			$this->SetTextColor($color[0], $color[1], $color[2]);
		}
		
		$drawColor = @$data["drawColor"];
		if ($drawColor)
		{
			$color = explode(",", $drawColor);
			$this->SetDrawColor($color[0], $color[1], $color[2]);
		}
		
		if (@$data["lineWidth"])
		{
			$this->SetLineWidth($data["lineWidth"]);
		}
	}
	
	function CheckPageBreak($h)
	{
		if($this->GetY() + $h > $this->PageBreakTrigger)
		{
			$this->AddPage($this->CurOrientation);
		}
	}

	function NbLines($w, $txt)
	{
		$cw = &$this->CurrentFont["cw"];
		if ($w == 0)
		{
			$w = $this->w - $this->rMargin - $this->x;
		}
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = str_replace("\r", "", $txt);
		$nb = strlen($s);
		if ($nb > 0 and $s[$nb - 1] == "\n")
		{
			$nb--;
		}
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		while ($i < $nb)
		{
			$c = $s[$i];
			if ($c == "\n")
			{
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
				continue;
			}
			if ($c == " ")
			{
				$sep = $i;
			}
			$l += $cw[$c];
			if ($l > $wmax)
			{
				if($sep == -1)
				{
					if($i == $j)
					{
						$i++;
					}
				}
				else
				{
					$i=$sep+1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
			}
			else
			{
				$i++;
			}
		}
		return $nl;
	}
}
?>