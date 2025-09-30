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
        <title>Generate ID Template</title>
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


        <!--complete list of meta tags at - https://gist.github.com/lancejpollard/1978404 -->
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div data-controller="/js/view/employee/employee_template_id_front">
            
            <input data-el="firstname" type="hidden" value="{{$employee->firstname}}"/>
            <input data-el="middlename" type="hidden" value="{{$employee->middlename}}"/>
            <input data-el="lastname" type="hidden" value="{{$employee->lastname}}"/>
            <input data-el="suffix" type="hidden" value="{{$employee->suffix}}"/>
            <input data-el="prefix" type="hidden" value="{{$employee->prefix}}"/>
            
            <input data-el="photo" type="hidden" value="/employee/photo/{{ $employee->photo }}"/>
            

            <input data-el="employee_id" type="hidden" value="{{$employee->id}}"/>
            <input data-el="position" type="hidden" value="{{$employee->position_options($employee->position) }}"/>
            
            <canvas id="front" data-el="canvas" width="638px" height="1016px"></canvas>

            <div style="display:none" id="qr_code"></div>
        </div>
        

        <div data-controller="/js/view/employee/employee_template_id_back">
            <canvas id="back" data-el="canvas" width="638px" height="1016px"></canvas>
            <input data-el="tin" type="hidden" value="{{$employee->tin}}"/>
            <input data-el="sss" type="hidden" value="{{$employee->sss}}"/>
            <input data-el="pag_ibig" type="hidden" value="{{$employee->pagibig}}"/>
        </div>

        <button style="width:638px; margin-top:20px; height:40px;cursor:pointer" onclick="window.download()">Download</button>
        
        <script type="module">
            import Technologia from '/technologia.js';
            import '/jszip.min.js';

            const front = document.querySelector('#front');
            const back  = document.querySelector('#back');

            Technologia.init(document.body);

            window.download = function(){
                const zip = new JSZip();
                
                front.toBlob((frontBlob)=>{

                    back.toBlob((backBlob)=>{

                        zip.file("{{$employee->lastname}}_{{$employee->firstname}}_front.png", frontBlob);
                        zip.file("{{$employee->lastname}}_{{$employee->firstname}}_back.png", backBlob);

                        zip.generateAsync({ type: "blob" })
                        .then(function(content) {
                            const link = document.createElement('a');
                            link.href = URL.createObjectURL(content);
                            link.download = "{{$employee->lastname}}_{{$employee->firstname}}_ID_Template.zip"; // Desired filename for the downloaded zip
                            link.click();
                            URL.revokeObjectURL(link.href); // Clean up the temporary URL
                        });

                    },'image/png');

                },'image/png');
            }
        </script>
    </body>
</html>