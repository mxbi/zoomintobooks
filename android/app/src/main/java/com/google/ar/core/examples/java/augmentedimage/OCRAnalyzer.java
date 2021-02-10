package com.google.ar.core.examples.java.augmentedimage;

import android.media.Image;

import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.android.gms.tasks.Task;
import com.google.mlkit.vision.common.InputImage;
import com.google.mlkit.vision.text.Text;
import com.google.mlkit.vision.text.TextRecognition;
import com.google.mlkit.vision.text.TextRecognizer;
import android.util.Log;

import androidx.annotation.NonNull;

public class OCRAnalyzer {
    private TextRecognizer recognizer = TextRecognition.getClient();
    // Only do one image at a time
    private boolean blocked = false;

    public void analyze(Image image) {
//        Log.i("", Integer.toString(image.getHeight()));

        // We only process one image at a time, even if ARCore takes many more
        // If we are mid-processing, we drop future requests
        if (blocked) {
            image.close();
            return;
        } else {
            blocked = true;
        }

        // Note: hard-coded vertical orientation. Might not work on other devices??
        InputImage inputImage = InputImage.fromMediaImage(image, 90);

        Task<Text> result = recognizer.process(inputImage)
                .addOnSuccessListener(new OnSuccessListener<Text>() {
                    @Override
                    public void onSuccess(Text text) {
                        Log.i("[OCRAnalyzer]", "Detected Text " + text.getText());
                        image.close();
                        blocked = false;
                    }
                })
                .addOnFailureListener(new OnFailureListener() {
                    @Override
                    public void onFailure(@NonNull Exception e) {
                        Log.e("[OCRAnalyzer]", "Failed: " + e.toString());
                        image.close();
                        blocked = false;
                    }
                });
    }
}
