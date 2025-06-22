<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h4>Purchase Requisition No: {{ $data['ticket'] }}
        @if ($data['status'] == 'Pending' || $data['status'] == 'Revised')
            <br> Status: Waiting for Approval
    </h4>
@elseif($data['status'] == 'Approved')
    <br> Status: Approved</h4>
@else
    <br> Status: Rejected</h4>
    @endif
    @if ($data['status'] == 'Pending' || $data['status'] == 'Revised')
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">PR ticket
            need to be review</a><br>
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">Click this
            link to view the PDF</a>
        Or manually type this in the url bar :
        http://{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ rawurlencode($data['ticket']) }}
    @elseif($data['status'] == 'Approved')
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">PR ticket
            has been Approved</a><br>
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">Click
            this link to view the PDF</a>
        Or manually type this in the url bar :
        http://{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ rawurlencode($data['ticket']) }}
    @else
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">PR
            ticket need to be revise</a><br>
        <a href="{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ $data['ticket'] }}">Click
            this link to view the PDF</a>
        Or manually type this in the url bar :
        http://{{ $serverIpAddress = getHostByName(gethostname()) }}:8000/printTicket/{{ rawurlencode($data['ticket']) }}
    @endif

</body>

</html>
