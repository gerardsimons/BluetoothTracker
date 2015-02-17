package com.simons.gerard.testapp;

/**
 * Created by Gerard on 15-2-2015.
 */
import android.graphics.Bitmap;
import android.content.Context;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.util.Log;
import android.view.View;

public class TemperatureIndicator extends View {

    private final static String TAG = "TemperatureIndicator";

    private Bitmap originalImage;
    private Bitmap scaledImage;

    private float strengthValue;

    private int indicatorColor = Color.BLACK;
    private int[] backgroundIndicatorColor = new int[]{200,200,200};

    private int strokeWidth;

    private Paint paint;
    private RectF rectangle;

    //Latest dimensions
    private int width,height;
    private float radius;

    private boolean uiDimensionsDetermined = false;

    public TemperatureIndicator(Context context) {
        super(context);
        setupPaint();
    }

    public TemperatureIndicator(Context context, AttributeSet attributeSet) {
        super(context,attributeSet);
        setupPaint();
    }

    private void setupPaint() {
        paint = new Paint();

        paint.setAntiAlias(true);
        paint.setFilterBitmap(true);
        paint.setDither(true);

        paint.setStyle(Paint.Style.STROKE);
    }

    /**
     * Call this method whenever canvas dimensions change
     */
    private void determineUIDimensions() {
        rectangle = new RectF();

        int minDim = Math.min(width,height);
        strokeWidth = Math.round(.2F * minDim);

        radius = minDim / 2F - strokeWidth / 2F;

        rectangle.set(width / 2 - radius, height / 2 - radius, width / 2
                + radius, height / 2 + radius);

        paint.setStrokeWidth(strokeWidth);
    }

    public void setImage(int id) {
        originalImage = BitmapFactory.decodeResource(getResources(),id);
        if(uiDimensionsDetermined) {
            createScaledImage();
        }
    }

    private void createScaledImage() {
        if(originalImage != null) {
            int minDimension = Math.min(width,height) - strokeWidth;
            scaledImage = Bitmap.createScaledBitmap( originalImage, minDimension, minDimension, true );
        }
    }

    public void setStrengthValue(float newValue) {
        if(newValue >= 0 && newValue <= 1) {
            strengthValue = newValue;
        }
        else throw new IllegalArgumentException("Strength value should be between 0 and 1");
    }

    @Override
    public void onSizeChanged(int newWidth, int newHeight, int oldWidth, int oldHeight) {
        Log.d(TAG,"Size changed.");
        super.onSizeChanged(newWidth,newHeight,oldWidth,oldHeight);

        width = newWidth;
        height = newHeight;

        uiDimensionsDetermined = false;
    }

    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        if(!uiDimensionsDetermined) {
            width = canvas.getWidth();
            height = canvas.getHeight();

            determineUIDimensions();

            createScaledImage();
        }

        paint.setARGB(255, backgroundIndicatorColor[0], backgroundIndicatorColor[1], backgroundIndicatorColor[2]);

        //Background color around icon
        canvas.drawArc(rectangle,0,360F,false,paint);

        paint.setColor(indicatorColor);

        canvas.drawArc(rectangle,-90F,strengthValue * 360F, false, paint);

        float centerX = width / 2F;
        float centerY = height / 2F;

        if(scaledImage != null) {
            canvas.drawBitmap(scaledImage,centerX - scaledImage.getWidth() / 2F, centerY - scaledImage.getHeight() / 2F,paint);
        }
        else Log.e(TAG,"No image set");



        //Draw image in center
    }
}
