package com.example.barcodescanner;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;

import com.google.zxing.integration.android.IntentIntegrator;
import com.google.zxing.integration.android.IntentResult;

public class MainActivity extends AppCompatActivity {

    private TextView textView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        textView = findViewById(R.id.textView);
        ActivityCompat.requestPermissions(this, new String[]
                {Manifest.permission.CAMERA},
                PackageManager.PERMISSION_GRANTED);
    }

    /* private fun buildQrDetector() {
        detector = BarcodeDetector.Builder(this)
                .setBarcodeFormats(Barcode.QR_CODE)
//            .setBarcodeFormats(Barcode.ALL_FORMATS)
                .build() */

    public void ScanButton(View view){
        IntentIntegrator intentIntegrator = new IntentIntegrator(this);

        intentIntegrator.setCameraId(0); //takes the back camera
        intentIntegrator.setBarcodeImageEnabled(false); //if true, can also save the scan
        intentIntegrator.setDesiredBarcodeFormats(IntentIntegrator.ALL_CODE_TYPES);
        //intentIntegrator.setTorchEnabled(true);
        //can specify other parameters here, such as timeout, ask for permission, ...

        intentIntegrator.initiateScan();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        IntentResult intentResult = IntentIntegrator.parseActivityResult(requestCode, resultCode, data);
        if (intentResult != null) { //we got something
            if (intentResult.getContents() == null) { //something went wrong
                textView.setText("Scan unsuccessful");
            } else {
                textView.setText(intentResult.getContents());
            }
        }
        super.onActivityResult(requestCode, resultCode, data);
    }
}