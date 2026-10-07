<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Hadir</th>
            <th>Izin</th>
            <th>Sakit</th>
            <th>Alpha</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($siswas as $index => $siswa)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $siswa['nama'] }}</td>
            <td>{{ $siswa['kelas'] }}</td>
            <td>{{ $siswa['hadir'] }}</td>
            <td>{{ $siswa['izin'] }}</td>
            <td>{{ $siswa['sakit'] }}</td>
            <td>{{ $siswa['alpha'] }}</td>
            <td>{{ $siswa['hadir'] + $siswa['izin'] + $siswa['sakit'] + $siswa['alpha'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
