<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Izin Lembur - PDF</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
             !important
        }


        .text-center {
            text-align: center;
        }

        #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #212529;
            color: white;
        }
    </style>
</head>

<body>
    <h2 class="text-center">IZIN LEMBUR</h2>
    <hr>
    {{-- <p style="font-size: 12px; text-align: right">Tanggal: {{
        Carbon\Carbon::parse($str_date)->translatedFormat('d-m-Y')}} s/d {{
        Carbon\Carbon::parse($n_date)->translatedFormat('d-m-Y')}}</p> --}}
    <table id="customers" style="font-size: 12px; margin-bottom: 30px">
        <thead>
            <tr>
                <th style="text-align: center">No.</th>
                <th style="text-align: center">Nama User</th>
                <th style="text-align: center">Perusahaan</th>
                <th style="text-align: center">Tgl. Lembur</th>
                <th style="text-align: center">Status Hari</th>
                <th style="text-align: center">Jam Mulai</th>
                <th style="text-align: center">Jam Selesai</th>
                <th style="text-align: center">Lama Lembur</th>
                <th style="text-align: center">Keterangan</th>
                <th style="text-align: center">Tarif Lembur Per Jam</th>
                <th style="text-align: center">Uang Makan</th>
                <th style="text-align: center">Tarif Lumsum</th>
                <th style="text-align: center">Total</th>
                <th style="text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
            $grandTotal = 0;
            @endphp

            @foreach($records as $record)
            @php
            $total = $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['total'] ?? 0;
            $grandTotal += $total;
            @endphp
            <tr>
                <td>
                    {{$loop->iteration }}
                </td>
                <td>{{ $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['user']['first_name']."
                    ".$record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['user']['last_name'] }}</td>
                <td>{{ $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['user']['company']['slug'] }}
                </td>
                <td>{{
                    Carbon\Carbon::parse($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['tanggal_lembur'])->translatedFormat('d/m/Y')
                    ?? '-'}}
                </td>
                <td>{{
                    $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['tarifLembur']['status_hari']}}
                </td>

                <td>{{
                    Carbon\Carbon::parse($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['start_time'])->translatedFormat('H:i')
                    ?? '-'}}
                </td>
                <td>{{
                    Carbon\Carbon::parse($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['end_time'])->translatedFormat('H:i')
                    ?? '-'}}
                </td>
                <td>{{ $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['lama_lembur'] }} jam
                <td>{{ $record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['keterangan_lembur'] }}
                <td>Rp {{
                    number_format($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['tarifLembur']['tarif_lembur_perjam'],
                    2, ",", ".") ?? 0 }}
                </td>
                <td>Rp {{
                    number_format($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['tarifLembur']['uang_makan'],
                    2,
                    ",", ".") ?? 0 }}</td>
                <td>Rp {{
                    number_format($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['tarifLembur']['tarif_lumsum'],
                    2, ",", ".") ?? 0 }}</td>
                <td>Rp {{ number_format($record['izinLemburApproveDua']['izinLemburApprove']['izinLembur']['total'], 2,
                    ",", ".") ?? 0
                    }}
                </td>
                <td>{{ $record['status'] == 0 ? 'Processing' : ($record['status'] == 1 ? 'Approved' : 'Rejected') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="12" style="text-align: right;">Grand Total</td>
                <td colspan="2" style="text-align: center;">Rp {{ number_format($grandTotal, 2, ",", ".") }}</td>
            </tr>
        </tfoot>
    </table>
    <div style="font-size: 12px; margin-top: -20px">
        {{-- <p><span style="margin-right: 53px">Data</span> : Total data {{$record->count()}}, jumlah data approve
            {{$record->where('status', 1)->count()}}, jumlah data reject {{$record->where('status',
            2)->count()}}, jumlah data proccessing {{$record->where('status',
            0)->count()}}
        </p> --}}
        <p><span style="margin-right: 15px; margin-top: -10px">Didownload</span> : {{
            Carbon\Carbon::now()->translatedFormat('d/m/Y, H:i')
            }}</p>
    </div>
</body>

</html>