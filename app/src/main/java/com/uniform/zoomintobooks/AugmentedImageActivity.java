package com.uniform.zoomintobooks;

import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.opengl.GLES20;
import android.opengl.GLSurfaceView;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.util.Log;
import android.util.Pair;
import android.view.Gravity;
import android.view.MotionEvent;
import android.view.View;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.bumptech.glide.Glide;
import com.bumptech.glide.RequestManager;
import com.google.ar.core.Anchor;
import com.google.ar.core.ArCoreApk;
import com.google.ar.core.AugmentedImage;
import com.google.ar.core.AugmentedImageDatabase;
import com.google.ar.core.Camera;
import com.google.ar.core.Config;
import com.google.ar.core.Frame;
import com.google.ar.core.Pose;
import com.google.ar.core.Session;
import com.google.mlkit.vision.common.InputImage;
import com.uniform.zoomintobooks.common.helpers.OCRAnalyzer;
import com.uniform.zoomintobooks.common.helpers.TextureReader;
import com.uniform.zoomintobooks.common.helpers.TextureReaderImage;
import com.uniform.zoomintobooks.rendering.AugmentedImageRenderer;
import com.uniform.zoomintobooks.common.helpers.AugmentedImageState;
import com.uniform.zoomintobooks.common.helpers.BookResource;
import com.uniform.zoomintobooks.common.helpers.CameraPermissionHelper;
import com.uniform.zoomintobooks.common.helpers.DisplayRotationHelper;
import com.uniform.zoomintobooks.common.helpers.FullScreenHelper;
import com.uniform.zoomintobooks.common.helpers.ZoomUtils;
import com.uniform.zoomintobooks.common.helpers.SnackbarHelper;
import com.uniform.zoomintobooks.common.helpers.TrackingStateHelper;
import com.uniform.zoomintobooks.common.rendering.BackgroundRenderer;
import com.google.ar.core.exceptions.CameraNotAvailableException;
import com.google.ar.core.exceptions.UnavailableApkTooOldException;
import com.google.ar.core.exceptions.UnavailableArcoreNotInstalledException;
import com.google.ar.core.exceptions.UnavailableSdkTooOldException;
import com.google.ar.core.exceptions.UnavailableUserDeclinedInstallationException;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.microedition.khronos.egl.EGLConfig;
import javax.microedition.khronos.opengles.GL10;

public class AugmentedImageActivity extends AppCompatActivity implements GLSurfaceView.Renderer {
  private static final String TAG = AugmentedImageActivity.class.getSimpleName();

  // Rendering. The Renderers are created here, and initialized when the GL surface is created.
  private GLSurfaceView surfaceView;
  private ImageView fitToScanView;
  private RequestManager glideRequestManager;
  private OCRAnalyzer ocrAnalyzer;

  private boolean installRequested;

  private Session session;
  private final SnackbarHelper messageSnackbarHelper = new SnackbarHelper();
  private DisplayRotationHelper displayRotationHelper;
  private final TrackingStateHelper trackingStateHelper = new TrackingStateHelper(this);

  private final BackgroundRenderer backgroundRenderer = new BackgroundRenderer();
  private final AugmentedImageRenderer augmentedImageRenderer = new AugmentedImageRenderer();

  private boolean shouldConfigureSession = false;
  private int gpuDownloadFrameBufferIndex = -1;

  private final TextureReader textureReader = new TextureReader();

  private final Map<Integer, Pair<AugmentedImageState, Anchor>> augmentedImageMap = new HashMap<>();
  private final List<BookResource> ARResources = new ArrayList<>();
  private final String encodedDB = "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";
  private float[] overlayCoords = new float[4];
  private final BookResource dummyResource = new BookResource(0, "https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/The_Calling_of_Saint_Matthew-Caravaggo_%281599-1600%29.jpg/800px-The_Calling_of_Saint_Matthew-Caravaggo_%281599-1600%29.jpg", true, "overlay");

  private int height = 0;
  private int width = 0;

  @Override
  protected void onCreate(Bundle savedInstanceState) {
    super.onCreate(savedInstanceState);

    ARResources.add(dummyResource);
    //TODO: get values needed for OCR and AR from the main activity
    Intent intent = new Intent();

    setContentView(R.layout.activity_augimg);
    surfaceView = findViewById(R.id.surfaceview);
    displayRotationHelper = new DisplayRotationHelper(/*context=*/ this);

    // Set up renderer.
    surfaceView.setPreserveEGLContextOnPause(true);
    surfaceView.setEGLContextClientVersion(2);
    surfaceView.setEGLConfigChooser(8, 8, 8, 8, 16, 0); // Alpha used for plane blending.
    surfaceView.setRenderer(this);
    surfaceView.setRenderMode(GLSurfaceView.RENDERMODE_CONTINUOUSLY);
    surfaceView.setWillNotDraw(false);

    fitToScanView = findViewById(R.id.image_view_fit_to_scan);
    glideRequestManager = Glide.with(this);
    glideRequestManager
        .load(Uri.parse("file:///android_asset/fit_to_scan.png"))
        .into(fitToScanView);

    installRequested = false;

    //get information about height and width
    DisplayMetrics displayMetrics = new DisplayMetrics();
    getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
    height = displayMetrics.heightPixels;
    width = displayMetrics.widthPixels;

    try {
      //TODO: this should take in input from the API.
      ocrAnalyzer = new OCRAnalyzer(getApplicationContext().getAssets().openFd("computernetworks.json").getFileDescriptor(), this);
    } catch (IOException e) {
      e.printStackTrace();
    }
  }

  @Override
  protected void onDestroy() {
    if (session != null) {
      session.close();
      session = null;
    }

    super.onDestroy();
  }

  @Override
  protected void onResume() {
    super.onResume();

    if (session == null) {
      Exception exception = null;
      String message = null;
      try {
        switch (ArCoreApk.getInstance().requestInstall(this, !installRequested)) {
          case INSTALL_REQUESTED:
            installRequested = true;
            return;
          case INSTALLED:
            break;
        }

        //request camera permissions
        if (!CameraPermissionHelper.hasCameraPermission(this)) {
          CameraPermissionHelper.requestCameraPermission(this);
          return;
        }

        session = new Session(/* context = */ this);
      } catch (UnavailableArcoreNotInstalledException
          | UnavailableUserDeclinedInstallationException e) {
        message = "Please install ARCore";
        exception = e;
      } catch (UnavailableApkTooOldException e) {
        message = "Please update ARCore";
        exception = e;
      } catch (UnavailableSdkTooOldException e) {
        message = "Please update this app";
        exception = e;
      } catch (Exception e) {
        message = "This device does not support AR";
        exception = e;
      }

      if (message != null) {
        messageSnackbarHelper.showError(this, message);
        Log.e(TAG, "Exception creating session", exception);
        return;
      }

      shouldConfigureSession = true;
    }

    if (shouldConfigureSession) {
      configureSession();
      shouldConfigureSession = false;
    }

    try {
      session.resume();
    } catch (CameraNotAvailableException e) {
      messageSnackbarHelper.showError(this, "Camera not available. Try restarting the app.");
      session = null;
      return;
    }
    surfaceView.onResume();
    displayRotationHelper.onResume();

    fitToScanView.setVisibility(View.VISIBLE);
  }

  @Override
  public void onPause() {
    super.onPause();
    if (session != null) {
      displayRotationHelper.onPause();
      surfaceView.onPause();
      session.pause();
    }
  }

  @Override
  public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] results) {
    super.onRequestPermissionsResult(requestCode, permissions, results);
    if (!CameraPermissionHelper.hasCameraPermission(this)) {
      Toast.makeText(
              this, "Camera permissions are needed to run this application", Toast.LENGTH_LONG)
          .show();
      if (!CameraPermissionHelper.shouldShowRequestPermissionRationale(this)) {
        // Permission denied with checking "Do not ask again".
        CameraPermissionHelper.launchPermissionSettings(this);
      }
      finish();
    }
  }

  @Override
  public void onWindowFocusChanged(boolean hasFocus) {
    super.onWindowFocusChanged(hasFocus);
    FullScreenHelper.setFullScreenOnWindowFocusChanged(this, hasFocus);
  }

  @Override
  public void onSurfaceCreated(GL10 gl, EGLConfig config) {
    GLES20.glClearColor(0.1f, 0.1f, 0.1f, 1.0f);
    // Prepare the rendering objects. This involves reading shaders, so may throw an IOException.
    try {
      // Create the texture and pass it to ARCore session to be filled during update().
      backgroundRenderer.createOnGlThread(/*context=*/ this);
      augmentedImageRenderer.createOnGlThread(/*context=*/ this);

      textureReader.create(this, TextureReaderImage.IMAGE_FORMAT_RGBA, 1920, 1080, false);
    } catch (IOException e) {
      Log.e(TAG, "Failed to read an asset file", e);
    }
  }

  @Override
  public void onSurfaceChanged(GL10 gl, int width, int height) {
    displayRotationHelper.onSurfaceChanged(width, height);
    GLES20.glViewport(0, 0, width, height);
  }

  @Override
  public void onDrawFrame(GL10 gl) {
    // Clear screen to notify driver it should not load any pixels from previous frame.
    GLES20.glClear(GLES20.GL_COLOR_BUFFER_BIT | GLES20.GL_DEPTH_BUFFER_BIT);


    if (session == null) {
      return;
    }
    // Notify ARCore session that the view size changed so that the perspective matrix and
    // the video background can be properly adjusted.
    displayRotationHelper.updateSessionIfNeeded(session);

    try {
      session.setCameraTextureName(backgroundRenderer.getTextureId());

      // Obtain the current frame from ARSession. When the configuration is set to
      // UpdateMode.BLOCKING (it is by default), this will throttle the rendering to the
      // camera framerate.
      Frame frame = session.update();
      Camera camera = frame.getCamera();

      try {
        if (!ocrAnalyzer.isBlocked()) {
          gpuDownloadFrameBufferIndex =
                  textureReader.submitFrame(backgroundRenderer.getTextureId(), 1920, 1080);

          if (gpuDownloadFrameBufferIndex >= 0) {
            Log.d("[DEBUG]", "Submitting frame to OCRAnalyzer...");
            TextureReaderImage image = textureReader.acquireFrame(gpuDownloadFrameBufferIndex);

            // Steal frame from OpenGL
            byte[] byteArray = new byte[1920 * 1080 * 4];
            image.buffer.position(0);
            image.buffer.get(byteArray, 0, image.buffer.capacity());
            Bitmap bitmap = bitmapFromRgba(1920, 1080, byteArray);

            textureReader.releaseFrame(gpuDownloadFrameBufferIndex);

//            bitmap = Bitmap.createScaledBitmap(bitmap, 1280, 720, false);
//            Log.d("[DEBUG]", "Submitted...");

            // 90 degree rotation for portrait.
            ocrAnalyzer.analyze(InputImage.fromBitmap(bitmap, 90));

          }
        }
      } catch (Exception e) {
        e.printStackTrace();
      }

      // Keep the screen unlocked while tracking, but allow it to lock when tracking stops.
      trackingStateHelper.updateKeepScreenOnFlag(camera.getTrackingState());

      // If frame is ready, render camera preview image to the GL surface.
      backgroundRenderer.draw(frame);

      // Get projection matrix.
      float[] projmtx = new float[16];
      camera.getProjectionMatrix(projmtx, 0, 0.1f, 100.0f);

      // Get camera matrix and draw.
      float[] viewmtx = new float[16];
      camera.getViewMatrix(viewmtx, 0);

      // Compute lighting from average intensity of the image.
      final float[] colorCorrectionRgba = new float[4];
      frame.getLightEstimate().getColorCorrection(colorCorrectionRgba, 0);

      // Visualize augmented images.
      drawAugmentedImages(frame, projmtx, viewmtx, colorCorrectionRgba);
    } catch (Throwable t) {
      // Avoid crashing the application due to unhandled exceptions.
      Log.e(TAG, "Exception on the OpenGL thread", t);
    }
  }

  public static Bitmap bitmapFromRgba(int width, int height, byte[] bytes) {
    int[] pixels = new int[bytes.length / 4];
    int j = 0;

    for (int i = 0; i < pixels.length; i++) {
      int R = bytes[j++] & 0xff;
      int G = bytes[j++] & 0xff;
      int B = bytes[j++] & 0xff;
      int A = bytes[j++] & 0xff;

      int pixel = (A << 24) | (R << 16) | (G << 8) | B;
      pixels[i] = pixel;
    }


    Bitmap bitmap = Bitmap.createBitmap(width, height, Bitmap.Config.ARGB_8888);
    bitmap.setPixels(pixels, 0, width, 0, 0, width, height);
    return bitmap;
  }


  private void configureSession() {
    Config config = new Config(session);
    config.setFocusMode(Config.FocusMode.AUTO);
    if (!setupAugmentedImageDatabase(config)) {
      messageSnackbarHelper.showError(this, "Could not set up augmented image database");
    }
    session.configure(config);
  }

  private void drawAugmentedImages(
      Frame frame, float[] projmtx, float[] viewmtx, float[] colorCorrectionRgba) {
    Collection<AugmentedImage> updatedAugmentedImages =
        frame.getUpdatedTrackables(AugmentedImage.class);

    // Iterate to update augmentedImageMap, remove elements we cannot draw.
    for (AugmentedImage augmentedImage : updatedAugmentedImages) {
      switch (augmentedImage.getTrackingState()) {
        case PAUSED:
          // When an image is in PAUSED state, but the camera is not PAUSED, it has been detected,
          // but not yet tracked.
          FrameLayout fl = findViewById(R.id.myLayout);

          Button btn = new Button(this);
          btn.setText("Click to view resource.");
          FrameLayout.LayoutParams fp = new FrameLayout.LayoutParams(FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.WRAP_CONTENT);
          fp.gravity = Gravity.BOTTOM | Gravity.CENTER;
          btn.setLayoutParams(fp);
          btn.setBackgroundColor(0xFFC7AF8F);
          btn.setOnClickListener(v -> {
            //TODO: on click, open a new activity.
          });
          this.runOnUiThread(new Runnable() {
            public void run() {
              fl.addView(btn);
            }
          });

          break;

        case TRACKING:
          // Have to switch to UI Thread to update View.
          this.runOnUiThread(
              new Runnable() {
                @Override
                public void run() {
                  fitToScanView.setVisibility(View.GONE);
                }
              });

          // Create a new anchor for newly found images.
          if (!augmentedImageMap.containsKey(augmentedImage.getIndex())) {
            Anchor centerPoseAnchor = augmentedImage.createAnchor(augmentedImage.getCenterPose());

            int augIndex = augmentedImage.getIndex();
            BookResource matchResource = ARResources.get(augIndex);
            AugmentedImageState augmentedImageState;
            if (matchResource.isOverlayable()) {
              InputStream is = ZoomUtils.getImageData(matchResource.getURL());
              augmentedImageState = new AugmentedImageState(augmentedImage, true, is, this);
            }
            else {
              augmentedImageState = new AugmentedImageState(augmentedImage, false);
            }
            augmentedImageMap.put(augmentedImage.getIndex(), Pair.create(augmentedImageState, centerPoseAnchor));
          }
          break;

        case STOPPED:
          augmentedImageMap.remove(augmentedImage.getIndex());
          break;
        default:
          break;
      }
    }

    // Draw all images in augmentedImageMap
    for (Pair<AugmentedImageState, Anchor> pair : augmentedImageMap.values()) {
      AugmentedImage augmentedImage = pair.first.getAugImg();
      Anchor centerAnchor = augmentedImageMap.get(augmentedImage.getIndex()).second;
      switch (augmentedImage.getTrackingState()) {
        case TRACKING:
          //first check whether the distance to the camera is low
          //check whether we are to overlay this image
          Camera c = frame.getCamera();
          float dist = getDistance(c.getPose(), centerAnchor.getPose());
          if (dist < 0.08) {
            String text = "Please bring the image into focus.";
            messageSnackbarHelper.showMessage(this, text);
            break;
          }
          messageSnackbarHelper.hide(this);
          if (pair.first.getOverlay()) {
            overlayCoords = augmentedImageRenderer.drawOverlay(
                    viewmtx, projmtx, pair.first, centerAnchor, colorCorrectionRgba, height, width);
          }
          else {
            augmentedImageRenderer.drawPin(viewmtx, projmtx, augmentedImage, centerAnchor, colorCorrectionRgba);
          }
          break;
        default:
          break;
      }
    }
  }

  private float getDistance(Pose startPose, Pose endPose) {

    float dx = startPose.tx() - endPose.tx();
    float dy = startPose.ty() - endPose.ty();
    float dz = startPose.tz() - endPose.tz();

    return (float) Math.sqrt(dx*dx + dy*dy + dz*dz);
  }



  private boolean setupAugmentedImageDatabase(Config config) {
    AugmentedImageDatabase augmentedImageDatabase;

    try (InputStream is = ZoomUtils.parse(encodedDB)) {
      augmentedImageDatabase = AugmentedImageDatabase.deserialize(session, is);
    } catch (IOException e) {
      Log.e(TAG, "IO exception loading augmented image database.", e);
      return false;
    }

    config.setAugmentedImageDatabase(augmentedImageDatabase);
    return true;
  }

  public boolean onTouchEvent(MotionEvent event){
    float X_coord = event.getX();
    float Y_coord = event.getY();

    if (X_coord >= overlayCoords[0] && X_coord <= overlayCoords[1] && Y_coord >= overlayCoords[2] && Y_coord <= overlayCoords[3]) {
      //TODO: open new activity.
      System.out.println("Detected click.");
    }

    return true;
  }
}
