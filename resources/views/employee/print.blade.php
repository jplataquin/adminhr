<!DOCTYPE html>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en-US"> <!--<![endif]-->
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="pingback" href="https: //domainname.com/xmlrpc.php" />
        <title>Employee Masterlist</title>
        <!-- style and script resources -->
        <link rel="stylesheet" href="" media="all">
        <script type="text/javascript">
            window.$base_url = '{{ url('') }}';
        </script>
        <!--meta properties -->
        <meta name="description" content=" Your site description." />
        <!--detailed robots meta https://developers.google.com/search/reference/robots_meta_tag -->
        <meta name="robots" content="index, follow, max-snippet: -1, max-image-preview:large, max-video-preview: -1" />
        <link rel="canonical" href="" />
        <!--open graph meta tags for social sites and search engines-->
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="  Opengraph content 25 char are best" />
        <meta property="og:description" content="  #description." />
        <meta property="og:url" content="" />
        <meta property="og:site_name" content="" />
        <meta property="og:image" content="images//hom-banner-compressed.jpg" />
        <meta property="og:image:secure_url" content="images//hom-banner-compressed.jpg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="660" />
        <!--twitter description-->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:description" content="." />
        <meta name="twitter:title" content="" />
        <meta name="twitter:site" content="@" />
        <meta name="twitter:image" content="images/hom-banner-compressed.jpg" />
        <meta name="twitter:creator" content="@" />
        <!--opengraph tags for location or address for information panel in google-->
        <meta name="og:latitude" content="" />
        <meta name="og:longitude" content="" />
        <meta name="og:street-address" content="" />
        <meta name="og:locality" content="" />
        <meta name="og:region" content="" />
        <meta name="og:postal-code" content="" />
        <meta name="og:country-name" content="" />
        <!--search engine verification-->
        <meta name="google-site-verification" content="" />
        <meta name="yandex-verification" content="" />
        <!--powered by meta-->
        <meta name="generator" content="" />
        <!-- Site fevicon icons -->
        <meta name="msapplication-TileImage" content="images/icon/cropped-cropped-favicon-1-1-270x270.png" />

        <style>
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

            .text-left{
                text-align:left;
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
        <!--complete list of meta tags at - https://gist.github.com/lancejpollard/1978404 -->
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        
        @foreach($divisions as $division => $employees)
        <h1>{{ $division_options->$division }}</h1>
        <table border="1">
            <tr>
                @foreach($headers as $title=>$key)
                    <th>
                        {{$title}}
                    </th>
                @endforeach
            </tr>
            @foreach($employees as $employee)
            <tr>
                @foreach($headers as $title=>$key)
                    
                    @if(is_array($key))
                        <td style="{{ $key['style'] }}">
                            @if( is_callable($key['key']) )

                                {{$key['key']($employee)}}
                            
                            @else

                                @php 
                                
                                    $row_key = $key['key'] 
                                
                                @endphp

                                {{$employee->$row_key}}

                            @endif
                        </td>

                    @else
                        <td>
                            @if( is_callable($key) )

                                {{$key($employee)}}
                            
                            @else
                            
                                {{$employee->$key}}

                            @endif
                        </td>
                    @endif
                    
                @endforeach
            </tr>
            @endforeach
        </table>
        <br><br>
        @endforeach
        
        <script type="module">
        </script>
    </body>
</html>