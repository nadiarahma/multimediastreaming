<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use FFMpeg;

class AudioController extends Controller
{
    public function index() {
    	return view ('audio.indexAudio'); //return to view indexAudio
    }

	public function convert(Request $request) 
	{
		$time = time('now'); //get time now
		$milliseconds = round(microtime(true) * 1000); //get time in ms
		$bitrate = ($request->bitrate ? " -ab ".$request->bitrate : ""); //if user use bitrate to convert the audio
		$sample_rate = ($request->sample_rate ? " -ar ".$request->sample_rate : ""); //if user use sample_rate to convert the audio
		$channel = ($request->channel ? " -ac ".$request->channel : ""); //if user use channel (mono or stereo) to convert the video

		$file = $request->file('audio'); //get name of audio to $file
    	$nama = $time.'_'.$file->getClientOriginalName(); //name file that used to store in /uploads/original
    	$destinationPath = 'uploads/original'; //define destination path
		$destinationConvert = 'uploads/convert'; //define destination convert
		$nama_output = $time.'_'.explode(".", $file->getClientOriginalName())[0].".".$request->output_audio; //name file that used to store in /uploads/convert

		//convert audio with paramater that given by user
		$command = "ffmpeg -i ".public_path()."/".$destinationPath."/".$nama.$bitrate." ".$sample_rate.$channel." -y ".public_path()."/".$destinationConvert."/".$nama_output;

		if($file->move($destinationPath,$nama)) //get file in /uploads/original
		{
			exec($command, $output, $status); //exec the $command
			$millisecondsend = round(microtime(true) * 1000); //get time in ms after process convert
			$hasil = (float)$millisecondsend - $milliseconds; //difference beetween get time in ms before process and git time in ms after process convert

			//if got error
			if($status)
			{
				return redirect(route('audio.index'))->with('error', 'Gagal Convert Audio');		
			}
			
			//return to audio.index with button download
			return redirect(route('audio.index'))->
										with('success', array('message' => 'Sukses Convert Audio Selama '.$hasil.' ms. Klik tombol Download untuk download file hasil', "filename" => $nama_output));
		}
		//if there isn't file in /uploads/original
		else
		{
			return redirect(route('audio.index'))->with('error', 'Sukses Convert Audio');
		}
	}
	

	public function download(Request $request)
	{
		$target_file = public_path()."/uploads/convert/".$request->file_download; //get file in /uploads/convert

		if(!$request->file_download)
		{
			return redirect(route('audio.index'))->with('error', 'Gagal mengunduh Audio hasil convert');
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

		return redirect(route('audio.index'))->with('error', 'Gagal mengunduh Audio hasil convert');
	}
}
