<?php

/*

~/Installed/ffmpeg/ffmpeg -framerate 30 -i _cache_/out%010d.png -c:v libx264 -r 30 -pix_fmt yuv420p out.mp4
~/Installed/ffmpeg/ffmpeg -framerate 1 -i _cache_/out%010d.png -c:v libx264 -r 30 -pix_fmt yuv420p out.mp4

1280 x 720

*/


if( !is_dir( "_cache_" ) ) {

	mkdir( "_cache_" ) ;
	
}

$im1 = imagecreatefromstring ( file_get_contents( "in.png" ) ) ;

$im1w = imagesx( $im1 ) ;
$im1h = imagesy( $im1 ) ;

$im2 = imagecreatetruecolor( $im1w , $im1h ) ;

print( "$im1w x $im1h\n" ) ;

$p = array( 628 , 340 ) ;
$ppix = imagecolorsforindex( $im1 ,  imagecolorat( $im1 , $p[ 0 ] , $p[ 1 ] ) ) ;
$pcol = imagecolorallocatealpha( $im2 , $ppix[ 'red' ] , $ppix[ 'green' ] , $ppix[ 'blue' ] , $ppix[ 'alpha' ] ) ;

$col1 = imageColorAllocate( $im2 , 255 , 255 , 255 ) ;

$frame = 0 ;

for( $zf = 0.0 ; $zf <= 1 ; $zf += 0.001 ) {

	$time_start = microtime( true ) ;

	$t1 = $p[ 0 ] + 0.5 ;
	$t2 = $p[ 1 ] + 0.5 ;
	$t3 = $p[ 0 ] - $im1w / 2 ;
	$t4 = $p[ 1 ] - $im1h / 2 ;
	$t5 = $zf * ( 1 - $zf ) * $t3 ;
	$t6 = $zf * ( 1 - $zf ) * $t4 ;
	$t7 = $zf * $t1 ;
	$t8 = $zf * $t2 ;

	$t9  = $t7 + $t5 ;
	$t10 = $t8 + $t6 ;

	$t11 = 1 - $zf ;


	$tx1 = 999999 ;
	$tx2 = -1 ;
	$ty1 = 999999 ;
	$ty2 = -1 ;

	imagefilledrectangle( $im2 , 0 , 0 , $im1w , $im1h , $pcol ) ;

	if( $zf < 1 ) {

		for( $x = 0 ; $x < $im1w ; $x++ ) {

			for( $y = 0 ; $y < $im1h ; $y++ ) {

				$ox = abs( round( $t9 + $x * $t11 ) ) ;
				$oy = abs( round( $t10 + $y * $t11 ) ) ;

				if( $ox == $p[ 0 ] && $oy == $p[ 1 ] ) {

					if( $x < $tx1 ) $tx1 = $x ;
					if( $x > $tx2 ) $tx2 = $x ;
					if( $y < $ty1 ) $ty1 = $y ;
					if( $y > $ty2 ) $ty2 = $y ;

				}

				if( $ox == ( $p[ 0 ] + 1 ) && $oy == ( $p[ 1 ] + 1 ) ) {

					if( $x < $tx1 ) $tx1 = $x ;
					if( $x > $tx2 ) $tx2 = $x ;
					if( $y < $ty1 ) $ty1 = $y ;
					if( $y > $ty2 ) $ty2 = $y ;

				}		

				if( $ox >= $im1w ) {

					$ox = $im1w - 1 ;

				}		

				if( $oy >= $im1h ) {

					$oy = $im1h - 1 ;

				}		

		    	$pix = imagecolorsforindex( $im1 ,  imagecolorat( $im1 , $ox , $oy ) );
		    	$col = imagecolorallocatealpha( $im2 , $pix[ 'red' ] , $pix[ 'green' ] , $pix[ 'blue' ] , $pix[ 'alpha' ] ) ;

		    	imagesetpixel( $im2 , $x , $y , $col ) ;

			}

		}

	} else {

		$tx1 = 0 ;
		$ty1 = 0 ;
		$tx2 = $im1w ;
		$ty2 = $im1h ;

	}

	imagecopyresampled( $im2 , $im1 , $tx1 , $ty1 , 0 , 0 , $tx2 - $tx1 , $ty2 - $ty1 , $im1w , $im1h ) ;

	$time = round( microtime( true ) - $time_start , 3 ) ;

	print( "[ F{$frame} / Z{$zf} / T$time ]\n" ) ;

	$fid = str_pad( $frame , 10 , "0" , STR_PAD_LEFT ) ;

	imagepng( $im2 , "_cache_/out{$fid}.png" ) ;

	$frame++ ;

}

imagedestroy( $im1 ) ;
imagedestroy( $im2 ) ;
