<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Label</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .barcode-label {
            padding: 10px;
        }
        .barcode-image {
            width: 80%;
            max-width: 300px;
            height: auto;
            display: block;
            margin: 0 auto 10px;
        }
        .barcode-text {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="barcode-label">
        <img src="{{ $barcodeImage }}" class="barcode-image" alt="Barcode">
        <div class="barcode-text">{{ $productName }}</div>
    </div>
      <!-- Auto-download the PDF silently -->
    <iframe src="{{ url('/label/' . $fnsku . '/download') }}" style="display:none;"></iframe>
</body>
</html>
