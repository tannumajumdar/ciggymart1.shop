<?php 
class Queue
{
	public $a;
	public $front;
	public $rear;
	function queue()
	{
		$front=0;
		$rear=0;
	}
	function insert($i)
	{
		$this->a[$this->rear] = $i;
		$this->rear++;
	}
	function remove()
	{
		if($this->isempty())
		{
			return false;
		}

		return($this->a[$this->front++]);
	}
	function isempty()
	{
		if($this->front == $this->rear)
		return true;
		else
		return false;
		
	}
}
?>