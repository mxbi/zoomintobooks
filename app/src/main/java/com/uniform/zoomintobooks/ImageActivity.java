package com.uniform.zoomintobooks;

import android.Manifest;;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Environment;
import android.view.MotionEvent;
import android.view.ScaleGestureDetector;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;

import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import android.os.Bundle;

import androidx.core.content.ContextCompat;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;


public class ImageActivity extends AppCompatActivity implements View.OnClickListener {
    private ScaleGestureDetector mScaleGestureDetector;
    private float mScaleFactor = 1.0f;

    private ImageView imageView;
    private Button exportBtn;

    private Uri uri;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_image_view);

        imageView = findViewById(R.id.scalableImage);

        uri = (Uri) getIntent().getExtras().get("uri");
        imageView.setImageURI(uri);

        mScaleGestureDetector = new ScaleGestureDetector(this, new ScaleListener());

        exportBtn = findViewById(R.id.exportButton);

        exportBtn.setOnClickListener(this);
    }

    @Override
    public boolean onTouchEvent(MotionEvent event) {
        return mScaleGestureDetector.onTouchEvent(event);
    }

    private class ScaleListener extends ScaleGestureDetector.SimpleOnScaleGestureListener {

        @Override
        public boolean onScale(ScaleGestureDetector scaleGestureDetector){
            mScaleFactor *= scaleGestureDetector.getScaleFactor();
            imageView.setScaleX(mScaleFactor);
            imageView.setScaleY(mScaleFactor);
            return true;
        }
    }

    @Override
    public void onClick(View v) {
        if (v == exportBtn) {
            saveImage();
        }
    }

    private boolean requestPermissions() {
        return PackageManager.PERMISSION_GRANTED == ContextCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE);
    }

    private String GALLERY_DIRECTORY = Environment.getExternalStorageDirectory() + "/" + Environment.DIRECTORY_DOWNLOADS;


    public void saveImage(){
        boolean permissionGranted = requestPermissions();
        String uri = this.uri.toString();
        String filename = uri.substring(uri.lastIndexOf("/"));
        export(uri, GALLERY_DIRECTORY.concat(filename));

        Toast.makeText(this, "File saved to ".concat(GALLERY_DIRECTORY.concat(filename)), Toast.LENGTH_LONG).show();
    }

    private void export(String src, String dst){
        String state = Environment.getExternalStorageState();
        if (!Environment.MEDIA_MOUNTED.equals(state)) {
            return;
        }

        try {
            FileInputStream inputStream = null;
            FileOutputStream outputStream = null;

            File fileSrc = new File(src);
            File fileDst = new File(dst);

            inputStream = new FileInputStream(fileSrc);
            outputStream = new FileOutputStream(fileDst, true);
            fileDst.createNewFile();

            byte[] buf = new byte[8192];
            int length = 0;
            while (true) {
                try {
                    if (!((length = inputStream.read(buf)) > 0)) break;
                } catch (IOException e) {
                    e.printStackTrace();
                }
                outputStream.write(buf, 0, length);
            }

            outputStream.close();
            inputStream.close();

        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }


    }
}