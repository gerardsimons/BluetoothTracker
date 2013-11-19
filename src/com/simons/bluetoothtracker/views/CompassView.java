package com.simons.bluetoothtracker.views;

import java.util.ArrayList;
import java.util.List;
import java.util.Random;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Paint.Style;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.util.Log;
import android.view.View;

import com.simons.bluetoothtracker.Fragment;
import com.simons.bluetoothtracker.Utilities;

public class CompassView extends View {

	private static final String TAG = CompassView.class.getSimpleName();
	private Paint paint;

	private RectF rectangle;
	private Random rand;

	// Random colors for testing
	private int[][] colors;
	private int[][] fragmentColors;

	private int strokeWidth = 100;

	private final int[] red = new int[] { 255, 0, 0 };
	private final int[] green = new int[] { 0, 255, 0 };
	private final int[] gray = new int[] { 105, 105, 105 };

	private int nrOfFragments;
	private List<Fragment> fragments;

	private boolean isCalibrated = false;

	private double rotation = 0D;

	private double pointer = Double.NaN;
	private final float pointerWidth = 20F;

	public CompassView(Context context) {
		super(context);
		initialize();
	}

	public CompassView(Context context, AttributeSet set) {
		super(context, set);
		initialize();

		initRandomColors();
	}

	private void initialize() {
		paint = new Paint();
		paint.setColor(Color.BLACK);
		paint.setStrokeWidth(strokeWidth);
		paint.setAntiAlias(true);
		paint.setStrokeCap(Paint.Cap.BUTT);
		paint.setStyle(Paint.Style.STROKE);

		// Object initialization
		rand = new Random();
		rectangle = new RectF();

	}

	@Override
	protected void onDraw(Canvas canvas) {
		super.onDraw(canvas);

		float width = canvas.getWidth();
		float height = canvas.getHeight();

		int cx = Math.round(width / 2F);
		int cy = Math.round(height / 2F);

		paint.setStyle(Style.STROKE);
		// Outer circle
		float radius = Math.min(width, height) / 2.5F - strokeWidth / 2.5F;

		// Example values
		rectangle.set(width / 2 - radius, height / 2 - radius, width / 2
				+ radius, height / 2 + radius);

		if (isCalibrated) {
			int nrOfFragments = fragments.size();
			float fragmentSize = 360F / nrOfFragments;
			for (int i = 0; i < nrOfFragments; i++) {
				// paint.setARGB(255, colors[i][0], colors[i][1], colors[i][2]);
				// Log.d(TAG, "Drawing interpolated colors.");
				float fragmentSizeRadians = (float) (2 * Math.PI / nrOfFragments);

				double rotationRadians = rotation * Math.PI / 180D;

				double nextRot = ((i + 1)) * fragmentSizeRadians;

				double x = Math.cos((i * fragmentSizeRadians + nextRot) / 2D
						+ rotationRadians)
						* radius;
				double y = Math.sin((i * fragmentSizeRadians + nextRot) / 2D
						+ rotationRadians)
						* radius;

				paint.setARGB(255, fragmentColors[i][0], fragmentColors[i][1],
						fragmentColors[i][2]);
				paint.setStyle(Style.STROKE);
				canvas.drawArc(rectangle,
						(float) (i * fragmentSize + rotation),
						fragmentSize * 1.01f, false, paint);

				if (!Double.isNaN(pointer)) {

					paint.setColor(Color.YELLOW);
					canvas.drawArc(rectangle, (float) pointer - pointerWidth
							/ 2F, pointerWidth, false, paint);
				}

				int textSize = 40;

				paint.setTextSize(50);
				paint.setStyle(Style.FILL);
				paint.setColor(Color.BLACK);

				int center = Math.round(textSize / 2F);

				canvas.drawText("" + fragments.get(i).getRepresentativeValue(),
						(float) x + cx - center, (float) y + cy + center, paint);
			}
		} else { // The compass is not yet calibrated, draw the uncalibrated
					// version
			float fragmentSizeRadians = (float) (2 * Math.PI / nrOfFragments);
			float fragmentSize = 360F / nrOfFragments;
			int activeIndex = fragmentIndexForAngle((float) rotation);
			for (int i = 0; i < nrOfFragments; i++) {
				// paint.setARGB(255, colors[i][0], colors[i][1], colors[i][2]);
				// Log.d(TAG, "Drawing interpolated colors.");
				Fragment fragment = fragments.get(i);

				double rotationRadians = rotation * Math.PI / 180D;

				double nextRot = ((i + 1)) * fragmentSizeRadians;

				double x = Math.cos((i * fragmentSizeRadians + nextRot) / 2D
						+ rotationRadians)
						* radius;
				double y = Math.sin((i * fragmentSizeRadians + nextRot) / 2D
						+ rotationRadians)
						* radius;

				int[] color = interpolateColors(i / (double) nrOfFragments,
						new int[] { 50, 50, 50 }, new int[] { 255, 255, 255 });

				// Log.d(TAG, "Color #" + i + " = " +
				// Utilities.arrayToString(color));

				paint.setStyle(Style.STROKE);
				// paint.setARGB(1, color[0], color[1], color[2]);

				if (i == activeIndex) {
					paint.setARGB(255, 45, 235, 255);
				} else {
					paint.setARGB(255, color[0], color[1], color[2]);
				}
				// paint.setColor(Color.RED);
				canvas.drawArc(rectangle,
						(float) (i * fragmentSize + rotation),
						fragmentSize * 1.01F, false, paint);

				int textSize = 40;

				paint.setTextSize(50);
				paint.setStyle(Style.FILL);
				paint.setColor(Color.BLACK);

				int center = Math.round(textSize / 2F);

				int calibrationValuesLeft = fragments.get(i)
						.valuesLeftForCalibration();
				if (calibrationValuesLeft > 0) {
					// canvas.drawText("" +
					// fragments.get(i).valuesLeftForCalibration(), (float) x +
					// cx - center, (float) y + cy + center, paint);
					canvas.drawText("" + calibrationValuesLeft, (float) x + cx
							- center, (float) y + cy + center, paint);
				} else {
					paint.setColor(Color.GREEN);
					canvas.drawText("" + fragment.getRepresentativeValue(),
							(float) x + cx - center, (float) y + cy + center,
							paint);
				}
			}
		}

		// Draw the angle
		paint.setStyle(Style.FILL);
		paint.setColor(Color.BLACK);
		paint.setTextSize(50);
		// String azimuth = new BigDecimal(String.valueOf(rotation)).setScale(5,
		// BigDecimal.ROUND_HALF_UP).toString();
		String azimuth = Math.round(rotation) + "";
		canvas.drawText(azimuth, cx, cy, paint);
	}

	public void setPointer(double pointer) {
		this.pointer = pointer;
	}

	private int fragmentIndexForAngle(float angle) {
		angle += 90F;
		angle = angle % 360F;
		angle = 360F - angle;
		return (int) Math.round(angle / 360F * (fragments.size() - 1));
	}

	private void initRandomColors() {
		// Random color generation
		colors = new int[12][3];
		for (int i = 0; i < colors.length; i++) {
			for (int j = 0; j < 3; j++) {
				colors[i][j] = Math.round(rand.nextFloat() * 256);
				// Log.d(TAG, "Colors[i][j]=" + colors[i][j]);
			}
		}

	}

	public void setNumberOfFragments(int nrOfFragments) {
		this.nrOfFragments = nrOfFragments;
	}

	public void setRotation(float angle) {
		// The given angle is in radions from -PI to PI.
		// First translate in range to [0,2*PI]
		// angle += Math.PI;

		rotation = angle;

		invalidate();

	}

	/**
	 * Determine the color for each fragment using linear interpolation
	 */
	private void calculateColors() {
		if (fragments != null) {
			double maxRSSI = Integer.MIN_VALUE;
			double minRSSI = Integer.MAX_VALUE;
			for (Fragment fragment : fragments) {

				double value = fragment.getRepresentativeValue();
				if (value > maxRSSI) {
					maxRSSI = value;
				} else if (value < minRSSI) {
					minRSSI = value;
				}
			}

			Log.d(TAG, "minRSSI = " + minRSSI);
			Log.d(TAG, "maxRSSI = " + maxRSSI);
			double delta = maxRSSI - minRSSI;
			// The second pass does the linear interpolation
			fragmentColors = new int[fragments.size()][3];
			for (int i = 0; i < fragments.size(); i++) {
				double ratio = (fragments.get(i).getRepresentativeValue() - minRSSI)
						/ delta;
				Log.d(TAG, "ratio = " + ratio);
				fragmentColors[i] = interpolateColors(ratio, green, red);
			}
		}
	}

	public int[] interpolateColors(double ratio, int[] colorOne, int[] colorTwo) {
		if (colorOne.length == colorTwo.length) {
			int[] interpolatedColors = new int[colorOne.length];
			for (int i = 0; i < colorOne.length; i++) {
				// Log.d(TAG, "channel #" + i);
				// Log.d(TAG, "min=" + min);
				// Log.d(TAG, "max=" + max);

				int newChannelValue = (int) Math.round((1 - ratio)
						* colorTwo[i] + ratio * colorOne[i]);
				// Log.d(TAG, "newChannelValue=" + newChannelValue);
				interpolatedColors[i] = newChannelValue;
			}
			return interpolatedColors;
		} else
			return null;
	}

	public void setCalibrated() {
		isCalibrated = true;
		calculateColors();
		invalidate();
	}

	public void setData(List<Fragment> fragments) {
		this.fragments = fragments;
	}
}
