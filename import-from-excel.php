<!-- Progress bar holder -->
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>
<?php
	// error_reporting ( E_ALL );
	// ini_set ( 'display_errors' , 1 );

	# MEMBUAT KONEKSI KE DATABASE
	mysql_connect('localhost',"root",'') or die("Koneksi gagal");
	mysql_select_db('excelsistem') or die("Database excelsistem tidak bisa diakses");

	include 'config/Classes/PHPExcel/IOFactory.php';

	if (!preg_match( "/.(xls|xlsx)$/i" , $_FILES["fileToUpload"]["name"] ) ) {
      echo "pastikan file yang anda pilih xls|xlsx";
      exit();
    } 
    else
    {
        $vdir_upload 	= "file/";
        $target 		= basename( $_FILES['fileToUpload']['name'] );
        $vfile_upload 	= $vdir_upload . $target;
        
        move_uploaded_file( $_FILES['fileToUpload']['tmp_name'] , $vfile_upload );
    	$file_name 		= $_FILES['fileToUpload']['tmp_name'];
        $inputFileName 	= $vfile_upload;
        if(file_exists($inputFileName))
        {
            // echo "Ya";
        }
    }

    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName); //Identify the file
        $objReader = PHPExcel_IOFactory::createReader($inputFileType); //Creating the reader
        $objPHPExcel = $objReader->load($inputFileName); //Loading the file
    } catch (Exception $e) {
        die( 'Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

  	$data 	= $objPHPExcel->getActiveSheet(0)->toArray();
	$error_count 	= 0;
	$error 	= array();
	$sukses = 0;
	// print_r($data);
	$total 	= count($data) - 1;
	// echo "Total Data : $total<br>";
	$i = 1;
	$j = 1;
	foreach ($data as $key => $val) {
		if ($key > 0 ) {
	      	if ($val[0]!='') {

	      		$nim 	= $val[0];
		      	$check 	= mysql_num_rows(mysql_query("SELECT * FROM siswa WHERE nis = '$nim' "));

		        if ($check != 0) {
					$error_count++;
					$error[] = $val[0]." Sudah Ada";
		        }
		        else
		        {
			        $sukses++;
		       		$data = array(
						 'nis'				=> $val[0],
						 'nama' 			=> $val[1],
						 'jenis_kelamin'	=> $val[2],
						 'alamat'			=> $val[3],
						 'kelas'			=> $val[4]
					);
					$data_push = $data;
					// print_r($data);
					// echo "<br>";
				
					//INSERT
					$dat = $data;
					//Proses Input
					if( $dat !== null )
			        $data 	= array_values( $dat );
			        //grab keys
			        $cols	= array_keys($dat);
			        $col 	= implode(', ', $cols);

			        //grab values and change it value
			        $mark=array();
			        foreach ($data as $key) {
			        	$keys='?';
			        	$mark[]= "\"$key\"";
			        }
			        $im = implode(', ', $mark);
			        $query_input = "INSERT INTO siswa ($col) values ($im)";

			        if(!mysql_query($query_input))
		            {
		                $error_count++;
		                $error[] = $val[0]." / ".$val[1]." SQL Gagal di Input.<Br>$query_input";
		            }
		        }//else of ($check != 0) {
			
        	}//else of ($val[0]!='') {

    	}//foreach ($data as $key => $val) {
    
	    // echo $j;
	    $percent = intval($i/$total * 100)."%";
	    
	    // Javascript for updating the progress bar and information
	    echo '
	    	<script language="javascript">
	    		document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#337ab7;\">&nbsp;</div>";
	    		document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
	    	</script>';

		// This is for the buffer achieve the minimum size in order to flush data
	    echo str_repeat(' ',1024*64);
	    
		// Send output to browser immediately
	    flush();

		// Sleep one second so we can see the delay
	    // sleep(0.5);
	    $i++;
		// Tell user that the process is completed

	}//end foreach

echo '<script language="javascript">
document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#337ab7;\">&nbsp;</div>";
document.getElementById("information").innerHTML="Process completed"
</script>';

//    hapus file xls yang udah dibaca
unlink("file/".$_FILES['fileToUpload']['name']);

echo "<h4>Status</h4> <Br>";
if (($sukses>0) || ($error_count > 0)) {
    $msg =  "<div class=\"alert alert-warning alert-dismissible\" role=\"alert\" >
        <font color=\"#3c763d\">".$sukses." data siswa baru berhasil di import</font><br />
        <font color=\"#ce4844\" >".$error_count." data tidak bisa ditambahkan </font>";
        if (!$error_count==0) {
          $msg .= ":";
        }
        //echo "<br />Total: ".$i." baris data";
        $msg .= "<div class=\"collapse\" id=\"collapseExample\">";
            $i=1;
            foreach ($error as $pesan) {
                $msg .= "<div class=\"bs-callout bs-callout-danger\">".$i.". ".$pesan."</div>";
              $i++;
              }
        $msg .= "</div>
      </div>";
  }
echo $msg;

?>

<br><br>
<a href='form-import.php'>Kembali</a>
