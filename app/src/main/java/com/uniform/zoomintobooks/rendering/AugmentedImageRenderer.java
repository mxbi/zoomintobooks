package com.uniform.zoomintobooks.rendering;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.opengl.Matrix;

import com.google.ar.core.Anchor;
import com.google.ar.core.AugmentedImage;
import com.google.ar.core.Pose;
import com.uniform.zoomintobooks.common.helpers.AugmentedImageState;
import com.uniform.zoomintobooks.common.rendering.ObjectRenderer;
import java.io.IOException;

/** Renders an augmented image. */
public class AugmentedImageRenderer {
    private static final String TAG = "AugmentedImageRenderer";

    private static final float TINT_INTENSITY = 0.1f;
    private static final float TINT_ALPHA = 1.0f;
    private static final int[] TINT_COLORS_HEX = {
            0x000000, 0xF44336, 0xE91E63, 0x9C27B0, 0x673AB7, 0x3F51B5, 0x2196F3, 0x03A9F4, 0x00BCD4,
            0x009688, 0x4CAF50, 0x8BC34A, 0xCDDC39, 0xFFEB3B, 0xFFC107, 0xFF9800,
    };


    private final ObjectRenderer pinRenderer = new ObjectRenderer();

    public AugmentedImageRenderer() {}

    public void createOnGlThread(Context context) throws IOException {
        Bitmap textureBitmap =
                BitmapFactory.decodeStream(context.getAssets().open("white-wood.jpg"));
        pinRenderer.createOnGlThread(context, "models/model.obj", textureBitmap);
        pinRenderer.setMaterialProperties(0.0f, 1.0f, 1.0f, 2.0f);
    }

    public void drawPin(float[] viewMatrix, float[] projectionMatrix, AugmentedImage augmentedImage, Anchor centerAnchor, float[] colorCorrectionRgba) {
        float[] tintColor = convertHexToColor(TINT_COLORS_HEX[augmentedImage.getIndex() % TINT_COLORS_HEX.length]);

        float block_edge_sizeX = 0.2f;
        float block_edge_sizeZ = 0.2f;

        float[] modelMatrix = new float[16];

        Pose anchorPose = centerAnchor.getPose();

        anchorPose.toMatrix(modelMatrix, 0);

        float blockScaleFactorX = augmentedImage.getExtentX()*0.1f/block_edge_sizeX;
        float blockScaleFactorZ = augmentedImage.getExtentZ()*0.1f/block_edge_sizeZ;
        pinRenderer.updateModelMatrix(modelMatrix, blockScaleFactorX, blockScaleFactorX, blockScaleFactorZ);
        pinRenderer.draw(viewMatrix, projectionMatrix, colorCorrectionRgba, tintColor);

    }

    public float[] drawOverlay(float[] viewMatrix, float[] projectionMatrix, AugmentedImageState augmentedImageState,
                               Anchor centerAnchor, float[] colorCorrectionRgba, int height, int width) {

        float[] tintColor = convertHexToColor(TINT_COLORS_HEX[augmentedImageState.getAugImg().getIndex() % TINT_COLORS_HEX.length]);

        float block_edge_sizeX = 2f;
        float block_edge_sizeZ = 2f;

        float[] modelMatrix = new float[16];

        Pose anchorPose = centerAnchor.getPose();

        anchorPose.toMatrix(modelMatrix, 0);

        float blockScaleFactorX = augmentedImageState.getAugImg().getExtentX()/block_edge_sizeX;
        float blockScaleFactorZ = augmentedImageState.getAugImg().getExtentZ()/block_edge_sizeZ;
        augmentedImageState.getRenderer().updateModelMatrix(modelMatrix, blockScaleFactorX, 1, blockScaleFactorZ);
        augmentedImageState.getRenderer().draw(viewMatrix, projectionMatrix, colorCorrectionRgba, tintColor);

        return info(modelMatrix, viewMatrix, projectionMatrix, height, width);

    }

    private static float[] convertHexToColor(int colorHex) {
        // colorHex is in 0xRRGGBB format
        float red = ((colorHex & 0xFF0000) >> 16) / 255.0f * TINT_INTENSITY;
        float green = ((colorHex & 0x00FF00) >> 8) / 255.0f * TINT_INTENSITY;
        float blue = (colorHex & 0x0000FF) / 255.0f * TINT_INTENSITY;
        return new float[] {red, green, blue, TINT_ALPHA};
    }

    private float[] info(float[] model, float[] view, float[] projection, int height, int width) {
        float[] modelView = new float[16];
        float[] modelViewProjection = new float[16];

        float[][] coords = new float[4][4];
        coords[0] = new float[] {-1f, 0f, 1f, 1};
        coords[1] = new float[] {1f, 0f, 1f, 1};
        coords[2] = new float[] {-1f, 0f, -1f, 1};
        coords[3] = new float[] {1, 0, -1, 1};
        float[][] endCoords = new float[4][2];

        Matrix.multiplyMM(modelView, 0, view,  0, model, 0);
        Matrix.multiplyMM(modelViewProjection, 0, projection, 0 , modelView, 0);

        for (int i = 0; i < 4; ++i) {
            Matrix.multiplyMV(coords[i], 0, modelViewProjection, 0, coords[i], 0);
            endCoords[i][0] = coords[i][0]/coords[i][3];
            endCoords[i][1] = coords[i][1]/coords[i][3];
        }

        float maxX = Float.MIN_VALUE;
        float minY = Float.MIN_VALUE;
        float minX = Float.MAX_VALUE;
        float maxY = Float.MAX_VALUE;

        for (int i = 0; i < 4; ++i) {
            maxX = Math.max(maxX, endCoords[i][0]);
            maxY = Math.min(maxY, endCoords[i][1]);
            minX = Math.min(minX, endCoords[i][0]);
            minY = Math.max(minY, endCoords[i][1]);
        }

        maxX = ((maxX+1)/2)*width;
        minX = ((minX+1)/2)*width;
        maxY = height*(1-((maxY+1)/2));
        minY = height*(1-((minY+1)/2));

        return new float[] {minX, maxX, minY, maxY};
    }
}
