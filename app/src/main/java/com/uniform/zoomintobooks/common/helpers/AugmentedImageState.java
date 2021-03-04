package com.uniform.zoomintobooks.common.helpers;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Log;

import com.google.ar.core.AugmentedImage;
import com.uniform.zoomintobooks.common.rendering.ObjectRenderer;

import java.io.IOException;
import java.io.InputStream;

public class AugmentedImageState {
    private final boolean overlay;
    private final AugmentedImage augImg;
    private Bitmap bmp = null;
    private final ObjectRenderer imageRenderer = new ObjectRenderer();
    private static final String TAG = "AugmentedImageRenderer";

    public AugmentedImageState(AugmentedImage img, boolean canOverlay) {
        this.overlay = canOverlay;
        this.augImg = img;
    }

    public AugmentedImageState(AugmentedImage img, boolean canOverlay, InputStream overlayStream, Context context) {
        this.overlay = canOverlay;
        this.bmp = BitmapFactory.decodeStream(overlayStream);
        this.imageRenderer.setMaterialProperties(1.0f, 0.0f, 0.0f, 1.0f);
        try {
            this.imageRenderer.createOnGlThread(context, "models/plane.obj", this.bmp);
        } catch (IOException e) {
            Log.e(TAG, "Failure while loading the overlay image");
        }
        this.augImg = img;

    }

    public Bitmap getBmp(){
        return bmp;
    }

    public AugmentedImage getAugImg() {
        return augImg;
    }


    public boolean getOverlay() {
        return overlay;
    }

    public ObjectRenderer getRenderer() {
        return this.imageRenderer;
    }
}
