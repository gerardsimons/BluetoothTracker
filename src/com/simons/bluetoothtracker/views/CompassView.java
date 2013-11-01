package com.simons.bluetoothtracker.views;

import java.util.Arrays;
import java.util.Random;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.util.Log;
import android.view.View;

public class CompassView extends View {

    private static final String TAG = CompassView.class.getSimpleName();
    private Paint paint;

    private RectF rectangle;
    private Random rand;

    private int[][] colors;

    private final int maxSize = 10000;
    private int size = 0;
    private int[] rssiValues;
    private float[] angles;

    //This value is chosen as it is out of the bounds of a possible RSSI measurement value, it acts as a de facto null value
    public static final int FRAGMENT_NOT_COMPUTED = 111;

    private int fragments = 4;
    private float[] fragmentValues;

    private float rotation = 0f;

    public CompassView(Context context) {
	super(context);
	initialize();
    }

    public CompassView(Context context, AttributeSet set) {
	super(context, set);
	initialize();

    }

    private void initialize() {
	paint = new Paint();
	paint.setColor(Color.BLACK);
	paint.setStrokeWidth(10);
	paint.setStyle(Paint.Style.STROKE);

	//Object initialization
	rand = new Random();
	rectangle = new RectF();

	rssiValues = new int[maxSize];
	angles = new float[maxSize];

	Arrays.fill(fragmentValues, FRAGMENT_NOT_COMPUTED);

	//Random color generation
	colors = new int[fragments][3];
	for (int i = 0; i < colors.length; i++) {
	    for (int j = 0; j < 3; j++) {
		colors[i][j] = Math.round(rand.nextFloat() * 256);
		Log.d(TAG, "Colors[i][j]=" + colors[i][j]);
	    }
	}
    }

    private float computeFragmentValues() {

    }

    @Override
    protected void onDraw(Canvas canvas) {
	super.onDraw(canvas);

	float width = canvas.getWidth();
	float height = canvas.getHeight();
	//Custom drawing

	//Outer circle
	float radius = .9F * Math.min(width, height) / 2F;

	//Example values
	rectangle.set(width / 2 - radius, height / 2 - radius, width / 2 + radius, height / 2 + radius);
	paint.setStrokeWidth(100);
	paint.setAntiAlias(true);
	paint.setStrokeCap(Paint.Cap.BUTT);
	paint.setStyle(Paint.Style.STROKE);

	Log.d(TAG, "rotation = " + rotation);

	float fragmentSize = 360F / fragments;
	for (int i = 0; i < fragments; i++) {
	    paint.setARGB(255, colors[i][0], colors[i][1], colors[i][2]);
	    canvas.drawArc(rectangle, i * fragmentSize + rotation, fragmentSize * 1.01f, false, paint);
	}
    }

    public void addData(int rssi, float angle) {

    }

    public void setRotation(float angle) {
	rotation = angle % 360F;
	//rotation += .01f;
	Log.d(TAG, "rotation set to " + rotation);

	invalidate();
    }
}
