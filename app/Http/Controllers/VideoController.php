<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FFMpeg;
// use FFProbe;


class VideoController extends Controller
{
    public function index() {
    	
    	return view('video.indexVideo');
    }


    public function convert(Request $request) {
    	
      //get value from user input
    	$height = $request->height;
    	$width = $request->width;
    	$bitrate = $request->bitrate;
    	$audioChannel = $request->audioChannel;
    	$fileFormat = $request->formatVideo;
    	$frameRate = $request->frameRate;

    	$path = public_path(); //define public path laravel
    	$time = time("now"); //to show date
    	$milliseconds = round(microtime(true) * 1000); //get time in ms
    	$file = $request->file('nama_video'); //set nama_image to $file

    	$take = $file->getClientOriginalName(); //get name of image
    	$takes = explode(".", $take); //separate name of image with "."
    	
    	$nama = $time."_".$file->getClientOriginalName(); //name file that used to store in /uploads/original
    	$nama_baru = $time."_".$takes[0].".".$request->formatVideo; //name file that used to store in /uploads/convert
    	$destinationPath = 'uploads/original'; //define destination path
    	$destinationConvert = 'uploads/convert'; //define destination convert

    	$execString = "ffmpeg -i /home/fourirakbar/Videos/". $take
	    		." -ac ". $audioChannel
	    		." -r ". $frameRate
	    		." -s ". $width. "x". $height 
	    		." -aspect ". $width .":". $height 
	    		." -b:v ". $bitrate ."k"
	    		." -bufsize ". $bitrate ."k"
	    		." -maxrate ". $bitrate ."k"
	    		." -sn -f ". $fileFormat 
	    		." -y ".$path."/uploads/convert/".$nama_baru;


    	if ($file->move($destinationPath,$nama)) { //get file in /uploads/original
    		
        //execute $execString
	    	exec($execString,
	    		$output, 
	    		$status);


	    	$millisecondsend = round(microtime(true) * 1000); //get time in ms after process convert
			$hasil = (float)$millisecondsend - $milliseconds; //difference beetween get time in ms before process and git time in ms after process convert 

		

			//if got error
			if($status)
			{
				return redirect(route('video.index'))->with('error', 'Gagal Convert Video');		
			}
			
        //return to video.index with button download
    		return redirect(route('video.index'))->
										with('success', array('message' => 'Sukses Convert Video Selama '.$hasil.' ms. Klik tombol Download untuk download file hasil', "filename" => $nama_baru));
	    }
	    else
	    {
			return redirect(route('video.index'))->with('error', 'Gagal Convert Video');		

	    }

	}



	public function download(Request $request)
	{
		$target_file = public_path()."/uploads/convert/".$request->file_download; //get file in /uploads/convert

		if(!$request->file_download)
		{
			return redirect(route('video.index'))->with('error', 'Gagal mengunduh video hasil convert');
		}

		if(file_exists($target_file)) //if there is file, download it
		{
			header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.explode('_',$request->file_download)[1].'"');
            header('Cache-Control: private');
            header('Content-Length: '.filesize($target_file));
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($target_file);
		}

		return redirect(route('video.index'))->with('error', 'Gagal mengunduh video hasil convert');
	}
}
