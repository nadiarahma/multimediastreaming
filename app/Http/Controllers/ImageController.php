<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index()  {
    	return view ('image.indexImage'); //return to view indexImage
    }

    public function convert(Request $request) {
    	$path = public_path(); //define public path laravel
    	$time = time("now"); //to show date
    	$milliseconds = round(microtime(true) * 1000); //get time in ms
    	$file = $request->file('nama_image'); //set nama_image to $file

    	$take = $file->getClientOriginalName(); //get name of image
    	$takes = explode(".", $take); //separate name of image with "."
    	
    	$nama = $time."_".$file->getClientOriginalName(); //name file that used to store in /uploads/original
    	$nama_baru = $time."_".$takes[0].".".$request->format_image; //name file that used to store in /uploads/convert
    	$destinationPath = 'uploads/original'; //define destination path
    	$destinationConvert = 'uploads/convert'; //define destination convert

    	if ($file->move($destinationPath,$nama)) { //get file in /uploads/original

    		//convert image with paramater that given by user
    		exec('ffmpeg -i /home/fourirakbar/Pictures/'.$take.' -vf scale='.$request->width.':'.$request->height.' '.$path.'/uploads/convert/'.$nama_baru.' ; convert '.$path.'/uploads/convert/'.$nama_baru.' -colorspace '.$request->colorspace.' -quality '.$request->conversion.'% '.$path.'/uploads/convert/'.$nama_baru ,$output, $status);

    		$millisecondsend = round(microtime(true) * 1000); //get time in ms after process convert
			$hasil = (float)$millisecondsend - $milliseconds; //difference beetween get time in ms before process and git time in ms after process convert

			if($status)
			{
				return redirect(route('image.index'))->with('error', 'Gagal Convert Image');		
			}

		//return to image.index with button download
    		return redirect(route('image.index'))->
										with('success', array('message' => 'Sukses Convert Image Selama '.$hasil.' ms. Klik tombol Download untuk download file hasil', "filename" => $nama_baru));
    	}
    	else
    	{
    		return redirect(route('image.index'))->with('error', 'Gagal Convert Image');
    	}
    	
    }

    public function download(Request $request)
	{
		$target_file = public_path()."/uploads/convert/".$request->file_download; //get file in /uploads/convert

		if(!$request->file_download)
		{
			return redirect(route('audio.index'))->with('error', 'Gagal mengunduh image hasil convert');
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

		return redirect(route('image.index'))->with('error', 'Gagal mengunduh image hasil convert');
	}
}
