<!DOCTYPE html>
<html>
    <head>
        <title>{{$account->name}} - {{$ledger->name}}</title>
        <style>
            body{
                font-size: 10px;
            }
            
            section {
                page-break-after: always; /* Start a new page after <section> elements */
            }

            table{
                width: 100%;
            }

            table, figure {
                page-break-inside: avoid; /* Prevent the table from breaking across pages */
            }

            table, tr, td, th {
                border: solid 1px #000000;
                border-collapse: collapse;
            }

            
            th, td {
                vertical-align: text-top;
                padding: 3px;
            }

            .text-center{
                text-align:center;
            }

            .text-right{
                text-align:right;
            }
            
            .mt-10px{
                margin-top:10px !important;
            }
            
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 0.5in;
                }

                body{
                    font-size: 10px;
                }

                section {
                    page-break-after: always; /* Start a new page after <section> elements */
                }

                table{
                    width: 100%;
                }

                table, figure {
                    page-break-inside: avoid; /* Prevent the table from breaking across pages */
                }

                table, tr, td, th {
                    border: solid 1px #000000;
                    border-collapse: collapse;
                }

                th, td {
                    vertical-align: text-top;
                    padding: 3px;
                }

                .text-center{
                    text-align:center;
                }

                .text-right{
                    text-align:right;
                }

                .mt-10px{
                    margin-top:10px !important;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
            <table>
                <tr>
                    <th width="25%">Account</th>
                    <td colspan="3">{{$account->name}}</td>
                </tr>
                <tr>
                    <th>Ledger</th>
                    <td colspan="3">{{$ledger->name}}</td>
                </tr>
                <tr>
                    <th width="25%">Status</th>
                    <td>{{$ledger->status}}</td>
                    <th>Unit</th>
                    <td>{{$ledger->unit}}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td colspan="3">{{$ledger->description}}</td>
                </tr>
            </table>

            <table class="mt-10px">
                <tr>
                    @php 
                        $total_credit = $ledger->getTotalCredit();
                        $total_debit  = $ledger->getTotalDebit();
                        $total_amount = $total_credit - $total_debit;
                    @endphp
                    <td class="text-center" width="25%">
                        <h3>{{ number_format($total_credit,2) }}</h3>
                        <p>(+) Credit</p>
                    </td>
                    <td class="text-center" width="25%">
                        <h3>{{ number_format($total_amount,2) }}</h3>
                        <p>Amount</p>
                    </td>
                    <td class="text-center" width="25%">
                        <h3>{{ number_format($ledger->getTotalQuantity(),2) }}</h3>
                        <p>{{$ledger->unit}}</p>
                    </td>
                    <td class="text-center" width="25%">
                        <h3>{{ number_format($total_debit,2) }}</h3>
                        <p>(-) Debit</p>
                    </td>
                </tr>
            </table>
            <table class="mt-10px">
                <tr>
                    <th>Tag</th>
                    <th>Particular</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Unit Amount</th>
                    <th>(+) Credit</th>
                    <th>(-) Debit</th>
                </tr>

                @foreach($entries as $entry)
                <tr>
                    <td width="10%" class="text-center">{{$entry->tag}}</td>
                    <td width="40%">{{$entry->particular}}</td>
                    <td class="text-center">{{$entry->date}}</td>
                    <td class="text-center">{{ number_format($entry->quantity, 2) }}</td>
                    <td class="text-center">{{$entry->unit_amount}}</td>
                    <td class="text-right">
                        @if($entry->type == 'CRED')
                            {{ number_format( ($entry->quantity * $entry->unit_amount),2 )}}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right">
                        @if($entry->type == 'DEBI')
                        {{ number_format( ($entry->quantity * $entry->unit_amount),2 )}}
                        @else
                            0.00
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
    </body>
</html>