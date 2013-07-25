<?php
class Controller_Media extends Controller {
	public function action_index()
	{
		$file = $this->request->param('file');

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media', $file, $ext))
		{
			
			// 3.1.2
			$this->response->check_cache(sha1($this->request->uri()).filemtime($file), $this->request);			
			// older versions
			//$this->request->check_cache(sha1($this->request->uri()).filemtime($file));
			
			$this->response->body(file_get_contents($file));
			$this->response->headers('content-length',  (string) filesize($file));
			$this->response->headers('content-type',  File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file)));
		}
		else
		{
			$this->response->status(404);
		}
	}	
}