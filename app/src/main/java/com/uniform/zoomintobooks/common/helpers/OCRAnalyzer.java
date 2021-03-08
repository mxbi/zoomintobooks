package com.uniform.zoomintobooks.common.helpers;

import android.content.Context;
import android.media.Image;

import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.android.gms.tasks.Task;
import com.google.ar.core.AugmentedImageDatabase;
import com.google.ar.core.Frame;
import com.google.ar.core.exceptions.NotYetAvailableException;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.gson.stream.JsonReader;
import com.google.mlkit.vision.common.InputImage;
import com.google.mlkit.vision.text.Text;
import com.google.mlkit.vision.text.TextRecognition;
import com.google.mlkit.vision.text.TextRecognizer;
import com.uniform.zoomintobooks.AugmentedImageActivity;

import android.net.Uri;
import android.os.Build;
import android.util.Log;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.File;
import java.io.FileDescriptor;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.lang.reflect.Type;
import java.net.URI;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Scanner;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.annotation.RequiresApi;
import me.xdrop.fuzzywuzzy.FuzzySearch;
import me.xdrop.fuzzywuzzy.model.ExtractedResult;

import static me.xdrop.fuzzywuzzy.FuzzySearch.ratio;

public class OCRAnalyzer {
    private TextRecognizer recognizer = TextRecognition.getClient();
    // Only do one image at a time
    private boolean blocked = false;
    private LinkedHashMap<Integer, String> textDatabase;
    private AugmentedImageActivity context;
    private Toast lastToast;

    private final String MATCHING_MODE = "full";
    private final int scoreThreshold = 75;

    private HashMap<String, BookResource> resourceMap = new HashMap<>();

    public boolean isBlocked() {
        return blocked;
    }

    public OCRAnalyzer(String ocrBlob, AugmentedImageActivity context, List<BookResource> ocrResources) {
        for (BookResource resource : ocrResources) {
            // no null check, NullPointerException here means server did not provide a page number, or the page number did not reach us for some reason
            resourceMap.put(resource.getOcrPageNumber(), resource);
        }

        Type type = new TypeToken<LinkedHashMap<Integer, String>>() {}.getType() ; // wtf

        this.textDatabase = new Gson().fromJson(ocrBlob, type);
        if (this.textDatabase.isEmpty()) {
            Log.e("[OCRAnalyzer]", "Text database empty!" + textDatabase.toString());
        }

        this.context = context;

    }

    @Nullable()
    public String matchText(Text text) {
        String t = text.getText();
        // Optional partial matching mode, we don't use this in practice
        if (t.length() > 100 && MATCHING_MODE.equals("partial")) {
            t = t.substring(t.length() / 2 - 50, t.length() / 2 + 50);
        }

        HashMap<Integer, Integer> scores = new HashMap<>();
        for (Entry<Integer, String> entry : textDatabase.entrySet()) {
            Integer score = MATCHING_MODE.equals("partial") ? FuzzySearch.partialRatio(t, entry.getValue()) : FuzzySearch.ratio(t, entry.getValue());
            scores.put(entry.getKey(), score);
        }

        Integer bestMatch = Collections.max(scores.entrySet(), Map.Entry.comparingByValue()).getKey();
        Integer bestScore = scores.get(bestMatch);

        Log.i("[OCRAnalyzer]", "Best match: page " + bestMatch.toString() + " score " + bestScore.toString());
        Log.d("[OCRAnalyzer]", scores.toString());
        if (bestScore > this.scoreThreshold) {
            if (lastToast != null) {
                lastToast.cancel();
            }
            lastToast = Toast.makeText(
                    context.getApplicationContext(), "MATCHED page " + bestMatch.toString() + " score " + bestScore.toString(), Toast.LENGTH_SHORT);
            lastToast.show();

            // Return match ID
            return bestMatch.toString();
        }

        return null;
    }

    public void analyze(Frame frame) {
        Image image;

        // We only process one image at a time, even if ARCore takes many more
        // If we are mid-processing, we drop future requests
        if (blocked) {
            //            image.close();
            return;
        } else {
            try {
                image = frame.acquireCameraImage();
            } catch (NotYetAvailableException e) {
                Log.w("[OCRAnalyzer]", "NotYetAvailableException");
                return;
            }
            blocked = true;
        }

        // Note: hard-coded vertical orientation. Might not work on other devices??
        InputImage inputImage = InputImage.fromMediaImage(image, 90);
        image.close();

        analyze(inputImage);
    }

    public void analyze(InputImage inputImage) {
        blocked = true;

        Task<Text> result = recognizer.process(inputImage)
                .addOnSuccessListener(new OnSuccessListener<Text>() {
                    @Override
                    public void onSuccess(Text text) {
                        Log.i("[OCRAnalyzer]", "Detected Text " + text.getText().length());

                        if (text.getText().length() > 100) {
                            long t = System.currentTimeMillis();
                            String match = matchText(text);
                            Log.v("[OCRAnalyzer]", "Search took " + Long.toString(System.currentTimeMillis() - t));

                            // We only want to open the resource if our activity is in the foreground
                            // i.e. the previous call hasn't opened a resource too
                            if (match != null && context.isForeground) {
                                BookResource detectedResource = resourceMap.get(match);
                                ((AugmentedImageActivity) context).displayResource(detectedResource);
                            }
                        }

                        blocked = false;
                    }
                })
                .addOnFailureListener(new OnFailureListener() {
                    @Override
                    public void onFailure(@NonNull Exception e) {
                        Log.e("[OCRAnalyzer]", "Failed: " + e.toString());
//                        image.close();
                        blocked = false;
                    }
                });
    }
}