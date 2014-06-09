sub SavePampersData {
	
	#Site_WDM_ID	firstName	lastName	gender	title	salutation	keycode	locale	WDM_campaign_ID	email	child_date_of_birth
	my $args = @_[1][0];
	chomp($args);
	my @row = qw(GB001592699704866854 Lesley Dallaway F Miss Dear 048668 en_GB 048668 lesleydallaway@hotmail.co.uk 20120728);
	my $Site_WDM_ID = $row[0];
	my $firstName= $row[1];
	my $lastName= $row[2];
	my $gender= $row[3];
	my $title= $row[4];
	my $salutation= $row[5];
	my $keycode= $row[6];
	my $locale= $row[7];
	my $WDM_campaign_ID = $row[8];
	my $email= $row[9];
	my $child_date_of_birth= $row[10];
	
	my $db = CARE::Connection->new();
    my $dbh = $db;

    my $q_string = "insert into ext_pampers_import_holding (Site_WDM_ID, firstName , lastName , gender , title , salutation , keycode , locale , WDM_campaign_ID, email , child_date_of_birth ) values
	('".$Site_WDM_ID."', '".$firstName."', '".$lastName."', '".$gender."', '".$title."', '".$salutation."', '".$keycode."', '".$locale."', '".$WDM_campaign_ID."', '".$email."', '".$child_date_of_birth."')";
    my $query = $dbh->prepare($q_string);
    my $res = $query->execute();
    
    if (!$res) {
        return "<res>0</res>";
    } else {
		return "<res>1</res>";
	}
   
}

=cut
	//filename , loaded_on  , loaded_by  , source_date  , notes
	// get the data and notes fields
	$header_data = array();
	$header_data[] =  $_POST["date"];
	$header_data[] = $_POST["notes"];
	$header_data[] = $_SESSION["user"];
	$header_data[] = date("d-m-Y H:i:s";
	$header_data[] = $_POST["file_name"];
=cut

sub SavePampersHeaders {
	my @args = @_[1][0];
	my @args = qw(Pampers_file_1 03-03-2014 Shahid 01-03-2014 Thisisatestnote);
	my $source_date = $args[0];
	my $notes = $args[1];
	my $loaded_by = $args[2];
	my $loaded_on = $args[3];
	my $filename = $args[4];
	
	
	my @warnings = ();
    my $db = CARE::Connection->new();
    my $dbh = $db;

    my $q_string = "insert into ext_pampers_import_header (filename , loaded_on  , loaded_by  , source_date  , notes ) values
	('".$filename."', '".$loaded_on."', '".$loaded_by."', '".$source_date."', '".$notes."')";
    my $query = $dbh->prepare($q_string);
    my $err_string = 'No results returned :' . $q_string;
    my $res = $query->execute();
    my $result = '';

	if (!$res) {
        return "<res>0</res>";
    } else {
		return "<res>1</res>";
	}
}

# Downloaded from the server

sub savePampersData
{
    #my @row = qw(GB001592699704866854 Lesley Dallaway F Miss Dear 048668 en_GB 048668 lesleydallaway@hotmail.co.uk 20120728);
		

    my $Site_WDM_ID = $_[1][0];
    my $firstName= $_[1][1];
    my $lastName= $_[1][2];
    my $gender= $_[1][3];
    my $title= $_[1][4];
    my $salutation= $_[1][5];
    my $keycode= $_[1][6];
    my $locale= $_[1][7];
    my $WDM_campaign_ID = $_[1][8];
    my $email= $_[1][9];
    my $child_date_of_birth= chomp($_[1][10]);
=cut

	my $Site_WDM_ID = $data[0];
    my $firstName= $data[1];
    my $lastName= $data[2];
    my $gender= $data[3];
    my $title= $data[4];
    my $salutation= $data[5];
    my $keycode= $data[6];
    my $locale= $data[7];
    my $WDM_campaign_ID = $data[8];
    my $email= $data[9];
    my $child_date_of_birth= $data[10];

=cut	
    my $db = CARE::Connection->new();
    my $dbh = $db;
 
    my $q_string = "insert into ext_pampers_import_holding (Site_WDM_ID, firstName , lastName , gender , title , salutation , keycode , locale , WDM_campaign_ID, email , child_date_of_birth ) values
        ('".$Site_WDM_ID."', '".$firstName."', '".$lastName."', '".$gender."', '".$title."', '".$salutation."', '".$keycode."', '".$locale."', '".$WDM_campaign_ID."', '".$email."', '".$child_date_of_birth."')";
    my $query = $dbh->prepare($q_string);
    my $res = $query->execute();

    if (!$res) {
        return "<res>0</res>";
    } else {
		return "<res>1</res>";
    }
}

sub savePampersHeaders
{
    my $filename = $_[1][0];
	my $source_date = $_[1][1];
    my $loaded_by = $_[1][2];
    my $loaded_on = $_[1][3];
    my $notes = $_[1][4];       
       
    my @warnings = ();
    my $db = CARE::Connection->new();
    my $dbh = $db;
 
    my $q_string = "insert into ext_pampers_import_header (filename , loaded_on  , loaded_by  , source_date  , notes ) values
        ('".$filename."', '".$loaded_on."', '".$loaded_by."', '".$source_date."', '".$notes."')";
    my $query = $dbh->prepare($q_string);
    my $err_string = 'No results returned :' . $q_string;
    my $res = $query->execute();
    my $result = '';
 
    if (!$res) {
        return "<res>0</res>";
    } else {
		return "<res>1</res>";
    }
}


