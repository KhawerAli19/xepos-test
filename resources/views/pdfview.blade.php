<style type="text/css">
    table td,
    table th {
        border: 1px solid black;
    }
</style>
<div class="container">


    <br />


    <table>
        <tr>
            <th>No</th>
            <th>Date</th>
            <th>Title</th>
            <th>Type</th>
        </tr>
        @foreach ($data as $key => $item)
            <tr>

                <td>{{ ++$key }}</td>
                <td>{{ $item->created_at->format('d/m/y') }}</td>
                <td>{{ $item->reminderable->appointment_title ?? 'Medicine' }}</td>
                <td>{{ $item->reminderable->type }}</td>
            </tr>
        @endforeach
    </table>
</div>
