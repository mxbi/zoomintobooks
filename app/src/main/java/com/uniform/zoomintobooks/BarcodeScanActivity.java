package com.uniform.zoomintobooks;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.util.Pair;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.Button;
import android.widget.TextView;

import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.zxing.integration.android.IntentIntegrator;
import com.google.zxing.integration.android.IntentResult;
import com.uniform.zoomintobooks.common.helpers.BookInfo;
import com.uniform.zoomintobooks.common.helpers.BookResource;
import com.uniform.zoomintobooks.common.helpers.ZoomUtils;

import java.io.IOException;


public class BarcodeScanActivity extends AppCompatActivity {
    Boolean MoreButtonOpen = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_barcode_scan);

        FloatingActionButton MoreButton = findViewById(R.id.MoreButton);
        MoreButton.setOnClickListener(v -> onMoreButtonClicked());

        FloatingActionButton ReturnButton = findViewById(R.id.Return);
        ReturnButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(),WelcomeActivity.class);
            startActivity(startIntent);
        });
        setMenuButtons();



        ActivityCompat.requestPermissions(this, new String[]
                        {Manifest.permission.CAMERA},
                PackageManager.PERMISSION_GRANTED);

        ScanButton(findViewById(R.id.nestedScrollView));


    }


    public void ScanButton(View view){
        IntentIntegrator intentIntegrator = new IntentIntegrator(this);

        intentIntegrator.setCameraId(0); //takes the back camera
        intentIntegrator.setBarcodeImageEnabled(false); //if true, can also save the scan
        intentIntegrator.setDesiredBarcodeFormats(IntentIntegrator.ALL_CODE_TYPES);
        //intentIntegrator.setTorchEnabled(true);
        //can specify other parameters here, such as timeout, ask for permission, ...

        intentIntegrator.initiateScan();
    }


    // We perform the HTTP lookups on an asynchronous thread to avoid locking up the UI
    private class BookScanTask extends AsyncTask<IntentResult, Void, Pair<IntentResult, String>> {
        @Override
        protected Pair<IntentResult, String> doInBackground(IntentResult... intentResults) {
            IntentResult intentResult = intentResults[0];
            String title = getBookName(intentResult.getContents());
            return new Pair(intentResult, title);
        }

        protected void onPostExecute(Pair<IntentResult, String> data) {
            IntentResult intentResult = data.first;
            String title = data.second;

            TextView results = findViewById(R.id.ScreenTitle);
            if(title.equals("error")) {
                results.setText(title);
            } else {
                String resultString = "Found:\n"+title;
                results.setText(resultString);
                Button ContinueButton = findViewById(R.id.ContinueButton);
                ContinueButton.setVisibility(View.VISIBLE);

                ContinueButton.setOnClickListener(v -> {
                    Intent startIntent = new Intent(getApplicationContext(),AugmentedImageActivity.class);
                    startIntent.putExtra("isbn",intentResult.getContents());
                    startActivity(startIntent);
                });
            }
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        TextView results = findViewById(R.id.ScreenTitle);
        Button AgainButton = findViewById(R.id.AgainButton);
        AgainButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(),BarcodeScanActivity.class);
            startActivity(startIntent);
        });

        IntentResult intentResult = IntentIntegrator.parseActivityResult(requestCode, resultCode, data);
        if (intentResult != null) { //we got something
            new BookScanTask().execute(intentResult);
        }


        super.onActivityResult(requestCode, resultCode, data);
    }

    private String getBookName(String contents) {
        String url = "https://api.uniform.ml/books/"+contents;
        BookInfo book =null;
        try {
            book = ZoomUtils.parseJSON(url);
        } catch (IOException e) {
            e.printStackTrace();
        }
        if(book==null){
            return "error";
        } else {
            return book.getTitle();
        }



    }

    private void setMenuButtons() {
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        InfoButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), InfoActivity.class);
            startIntent.putExtra("CurrentAct",BarcodeScanActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        BookButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), BookActivity.class);
            startIntent.putExtra("CurrentAct",BarcodeScanActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        ContactButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), ContactActivity.class);
            startIntent.putExtra("CurrentAct",BarcodeScanActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        SettingButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), SettingsActivity.class);
            startIntent.putExtra("CurrentAct",BarcodeScanActivity.class);
            startActivity(startIntent);
        });
    }

    private void onMoreButtonClicked() {
        setVisibilityOfMenu(MoreButtonOpen);
        setAnimationsOfMenu(MoreButtonOpen);
        MoreButtonOpen=!MoreButtonOpen;
    }

    private void setAnimationsOfMenu(Boolean moreButtonOpen) {
        Animation rotateOpen = AnimationUtils.loadAnimation(this, R.anim.rotate_open_anim);
        Animation rotateClose = AnimationUtils.loadAnimation(this, R.anim.rotate_close_anim);
        Animation fromBottom = AnimationUtils.loadAnimation(this, R.anim.from_bottom_anim);
        Animation toBottom = AnimationUtils.loadAnimation(this, R.anim.to_bottom_anim);
        FloatingActionButton MoreButton = findViewById(R.id.MoreButton);
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        if(!moreButtonOpen){
            MoreButton.setAnimation(rotateOpen);
            InfoButton.setAnimation(fromBottom);
            ContactButton.setAnimation(fromBottom);
            BookButton.setAnimation(fromBottom);
            SettingButton.setAnimation(fromBottom);
        } else {
            MoreButton.setAnimation(rotateClose);
            InfoButton.setAnimation(toBottom);
            ContactButton.setAnimation(toBottom);
            BookButton.setAnimation(toBottom);
            SettingButton.setAnimation(toBottom);
        }
    }

    private void setVisibilityOfMenu(Boolean moreButtonOpen) {
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        if(!moreButtonOpen){
            InfoButton.setVisibility(View.VISIBLE);
            ContactButton.setVisibility(View.VISIBLE);
            BookButton.setVisibility(View.VISIBLE);
            SettingButton.setVisibility(View.VISIBLE);
        } else {
            InfoButton.setVisibility(View.INVISIBLE);
            ContactButton.setVisibility(View.INVISIBLE);
            BookButton.setVisibility(View.INVISIBLE);
            SettingButton.setVisibility(View.INVISIBLE);
        }
    }


}