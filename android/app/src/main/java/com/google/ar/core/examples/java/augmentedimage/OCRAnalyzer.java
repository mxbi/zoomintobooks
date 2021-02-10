package com.google.ar.core.examples.java.augmentedimage;

import android.media.Image;

import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.android.gms.tasks.Task;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.gson.stream.JsonReader;
import com.google.mlkit.vision.common.InputImage;
import com.google.mlkit.vision.text.Text;
import com.google.mlkit.vision.text.TextRecognition;
import com.google.mlkit.vision.text.TextRecognizer;

import android.os.Build;
import android.util.Log;

import org.json.JSONObject;

import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.lang.reflect.Type;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;

import androidx.annotation.NonNull;
import androidx.annotation.RequiresApi;
import me.xdrop.fuzzywuzzy.FuzzySearch;

public class OCRAnalyzer {
    private TextRecognizer recognizer = TextRecognition.getClient();
    // Only do one image at a time
    private boolean blocked = false;
    private HashMap<String, Integer> textDatabase;
    
    public OCRAnalyzer(String textDatabase) {
        try {
            JsonReader reader = new JsonReader(new FileReader(textDatabase));
            Type type = new TypeToken<HashMap<String, Integer>>() {}.getType() ; // wtf

            this.textDatabase = new Gson().fromJson(reader, type);

        } catch (IOException e) {
            e.printStackTrace();
            this.textDatabase = new HashMap<>();
        }
    }

    public void matchText(Text text) {
        HashMap<Integer, Integer> scores = new HashMap<>();
        for (Entry<String, Integer> entry : textDatabase.entrySet()) {
            // TODO: Improve search
            Integer score = FuzzySearch.partialRatio(text.getText(), entry.getKey());
            scores.put(entry.getValue(), score);
        }
        Integer bestMatch = Collections.max(scores.entrySet(), Map.Entry.comparingByValue()).getKey();
        Integer bestScore = scores.get(bestMatch);

        Log.i("[OCRAnalyzer]", "Best match: page " + bestMatch.toString() + " score " + bestScore.toString());
    }

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
                        Log.i("[OCRAnalyzer]", "Detected Text " + text.getText().length());
                        image.close();

                        if (text.getText().length() > 10) {
                            matchText(text);
                        }

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
