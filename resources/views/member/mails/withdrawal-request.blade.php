<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('detailsApp.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>

<body>
    <h3>Dear! {{ $name }}</h3>
    <p class="mt-3">New withdrawal request created on Dated - {{ date('d-m-Y', strtotime($date)) }} by request id no.
        {{ $request_id }} for the gross amount - {{ $gross }}. Details are as follows.</p>
    <table class="table mt-4 text-start">

        <tr>
            <th>Name</th>
            <td>{{ $name }}</td>
        </tr>
        <tr>
            <th>Memberid</th>
            <td>{{ $memberid }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ $type }}</td>
        </tr>
        <tr>
            <th>Gross Amount</th>
            <td>{{ $gross }}</td>
        </tr>
        <tr>
            <th>Service Chargse</th>
            <td>{{ $service }}</td>
        </tr>
        <tr>
            <th>Net Amount</th>
            <td>{{ $net }}</td>
        </tr>
        <tr>
            <th>Pay Wallet</th>
            <td>{{ $wallet }}</td>
        </tr>
    </table>

    <h5>With Regard</h5>
    <h4>Team {{ config('detailsApp.name') }}</h4>
    <img src="{{ $message->embed(public_path() . '/logo/logo.png') }}" width="150" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
