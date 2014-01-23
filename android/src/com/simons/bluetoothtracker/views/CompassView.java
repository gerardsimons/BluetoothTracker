package com.simons.bluetoothtracker.views;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Paint.Style;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.util.Log;
import android.view.View;

import com.simons.bluetoothtracker.models.CompassDataSource;

import java.text.DecimalFormat;
import java.util.Random;

/**
 * This class is the view of a compass. It is setup as being a circle divided in several parts called
 * fragments. The compassview uses interfaces called CompassDataSources for getting the values to
 * determine the color of each fragment.
 *
 * The compass view exists in either a uncalibrated state, or a calibrated one. In the uncalibrated
 * state the compass looks at the values required for calibration. When calibrated the compass
 * indicates colors as a visualization of the signal strengths around the user. The compassview is
 * meant to be rotated according to the actual magnetic field (implementation not here)
 * @author Gerard Simons
 *
 */
public class CompassView extends View {

    private static final String TAG = CompassView.class.getSimpleName();
    private Paint paint;

    private RectF rectangle;
    private Random rand;

    // Random colors for testing
    private int[][] colors;
    private int[][] fragmentColors;

    private boolean uiDimensionsDetermined = false;

    //Dimension values for the compass
    private int width,height;
    private int cX,cY;
    private float radius;
    private int textSize = 30;
    private int strokeWidth = 200;

    private boolean drawDebugText = false;

    private CompassDataSource[] dataSources;

    private final int[] red = new int[]{255, 0, 0};
    private final int[] green = new int[]{0, 255, 0};
    private final int[] gray = new int[]{105, 105, 105};

    private final int[] highlightColor = new int[]{45,235,255};

    private final String decimalFormatString = "##.#";
    private DecimalFormat decimalFormat;

    private int nrOfFragments;

    private boolean isCalibrated = false;

    private double rotation = 0D;

    private final int recalculateColorsThrottle = 10;
    private int drawRound = 0;

    public CompassView(Context context) {
        super(context);
        initialize();
    }

    public CompassView(Context context, AttributeSet set) {
        super(context, set);
        initialize();

        initRandomColors();
    }

    /**
     * Initialize some basic objects used in the onDraw method
     */
    private void initialize() {
        decimalFormat = new DecimalFormat(decimalFormatString);

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
    protected void onSizeChanged(int w, int h, int oldw, int oldh) {
        super.onSizeChanged(w, h, oldw, oldh);
        uiDimensionsDetermined = false;
    }

    /**
     * Calculate the size of the compass and the font size according to the canvas size.
     * @param canvas the canvas which determines the space available for UI elements
     */
    private void determineUIDimensions(Canvas canvas) {
        width = canvas.getWidth();
        height = canvas.getHeight();

        int minDimension = Math.min(width,height);

        //Let a fragments width be proportional to the size of the screen
        strokeWidth = Math.round(.3F * minDimension);
        paint.setStrokeWidth(strokeWidth);

        textSize = Math.round(.05F * minDimension);
        Log.d(TAG, "New text size = " + textSize);

        cX = Math.round(width / 2F);
        cY = Math.round(height / 2F);

        // Outer circle
        radius = Math.min(width, height) / 2.2F - strokeWidth / 2.5F;

        // Example values
        rectangle.set(width / 2 - radius, height / 2 - radius, width / 2
                + radius, height / 2 + radius);



        uiDimensionsDetermined = true;
    }

    /**
     * Draws the compass. A compass is divided in fragments where all fragments together make a full circle of 360 degrees.
     * A fragment is drawn as an arced rectangle. The color of a fragment is either some gray color (when the compass is not yet calibrated)
     * or as a color between red and green to indicate signal strength in the given fragment.
     * Texts are also drawn.
     * @param canvas the canvas to draw on
     */
    @Override
    //TODO:Remove code duplicity! The only real difference between calibrated and uncalibrated is the color computation
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        if(!uiDimensionsDetermined) {
            determineUIDimensions(canvas);
        }

        paint.setStyle(Style.STROKE);

        float fragmentSize = 360F / nrOfFragments;

        if (isCalibrated) {
            calculateColors();
            for (int i = 0; i < nrOfFragments; i++) {

                CompassDataSource dataSource = dataSources[i];

                double value = dataSource.getValue();
                double calibrationValue = dataSource.getCalibrationValue();

                float fragmentSizeRadians = (float) (2 * Math.PI / nrOfFragments);

                double rotationRadians = rotation * Math.PI / 180D;

                double nextRot = ((i + 1)) * fragmentSizeRadians;

                double x = Math.cos((i * fragmentSizeRadians + nextRot) / 2D
                        + rotationRadians)
                        * radius;
                double y = Math.sin((i * fragmentSizeRadians + nextRot) / 2D
                        + rotationRadians)
                        * radius;

                paint.setARGB(255, fragmentColors[i][0], fragmentColors[i][1],fragmentColors[i][2]);
                paint.setStyle(Style.STROKE);

                //Use slightly more width to avoid gaps
                canvas.drawArc(rectangle,
                        (float) (i * fragmentSize + rotation),
                        fragmentSize * 1.01f, false, paint);

                if(drawDebugText) {
                    paint.setTextSize(textSize);
                    paint.setStyle(Style.FILL);
                    paint.setColor(Color.BLACK);

                    int center = Math.round(textSize / 2F);
                    canvas.drawText("" + decimalFormat.format(value) + "(" + decimalFormat.format(calibrationValue) + ") #" + dataSource.getId(),
                        (float) x + cX - center, (float) y + cY + center, paint);
                }
            }
        } else { // The compass is not yet calibrated, draw the uncalibrated version
            float fragmentSizeRadians = (float) (2 * Math.PI / nrOfFragments);

            for (int i = 0; i < nrOfFragments; i++) {
                double rotationRadians = rotation * Math.PI / 180D;

                double nextRot = ((i + 1)) * fragmentSizeRadians;

                double x = Math.cos((i * fragmentSizeRadians + nextRot) / 2D
                        + rotationRadians)
                        * radius;
                double y = Math.sin((i * fragmentSizeRadians + nextRot) / 2D
                        + rotationRadians)
                        * radius;

                CompassDataSource dataSource = dataSources[i];

                int valuesMeasured = Math.max(0,dataSource.getNrOfValuesMeasured());
                int calibrationLimit = dataSource.getCalibrationLimit();
                boolean highlighted = dataSource.highlighted();

                int[] color = interpolateColors((valuesMeasured / (double)calibrationLimit),
                        new int[]{100, 100, 100}, new int[]{255, 255, 255});

                paint.setStyle(Style.STROKE);

                if (highlighted) {
                    paint.setARGB(255,highlightColor[0],highlightColor[1],highlightColor[2]);
                } else {
                    paint.setARGB(255, color[0], color[1], color[2]);
                }

                canvas.drawArc(rectangle,
                        (float) (i * fragmentSize + rotation),
                        fragmentSize * 1.01F, false, paint);


                    paint.setTextSize(textSize);
                    paint.setStyle(Style.FILL);
                    paint.setColor(Color.BLACK);

                    int center = Math.round(textSize / 2F);

                String contextText = "" + valuesMeasured;
                if(drawDebugText)
                    contextText += " #" + dataSource.getId();

                canvas.drawText(contextText,
                        (float) x + cX - center, (float) y + cY + center, paint);


            }
            drawRound++;
        }

        if(drawDebugText) {
            // Draw the angle text
            paint.setStyle(Style.FILL);
            paint.setColor(Color.BLACK);

            String azimuth = Math.round(rotation) + "";
            canvas.drawText(azimuth, cX, cY, paint);
        }
    }

    /**
     * Random color generation, useful for testing
     */
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

    /**
     * The number of fragments this compass view should have
     * @param nrOfFragments
     */
    public void setNumberOfFragments(int nrOfFragments) {
        this.nrOfFragments = nrOfFragments;
    }

    /**
     * Set the rotation of the compass
     * @param angle the new rotation
     */
    public void setRotation(float angle) {
        rotation = angle;
        invalidate();
    }

    /**
     * Determine the color for each fragment using linear interpolation between red and green
     */
    private void calculateColors() {
        if (dataSources != null) {

            //First determine the min and max values.
            double maxRSSI = Integer.MIN_VALUE;
            double minRSSI = Integer.MAX_VALUE;
            for (CompassDataSource dataSource : dataSources) {
                double value = dataSource.getValue();
                if (value > maxRSSI) {
                    maxRSSI = value;
                }
                if (value < minRSSI) {
                    minRSSI = value;
                }
            }

            double delta = maxRSSI - minRSSI;

            // The second pass does the linear interpolation
            fragmentColors = new int[dataSources.length][3];
            for (int i = 0; i < dataSources.length ; i++) {
                double ratio = (dataSources[i].getValue() - minRSSI) / delta;
                fragmentColors[i] = interpolateColors(ratio, green, red);
            }
        }
    }

    /**
     * Returns the color interpolated between colorOne and colorTwo. Ratio should be on the closed
     * [0,1] interval, where 0 means a color equivalent to colorOne and 1 to colorTwo, arbitrary values
     * between 0 and 1 means a weighted average of the two
     * @param ratio a measure of interpolation
     * @param colorOne color parameter to interpolate from
     * @param colorTwo the other color parameter to interpolate from
     * @return the linearly interpolated color
     */
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

    /**
     * Set the compass view to draw the calibrated version. When this is set (typically by the compass
     * controller, the compassview will draw the fragments according to the datasource values.
     */
    public void setCalibrated() {
        isCalibrated = true;
        calculateColors();
        invalidate();
    }

    /**
     *  Sets the datasources to be used as sources for the fragments
     * @param dataSources the data sources to be used
     */
    public void setDataSources(CompassDataSource[] dataSources) {
        this.dataSources = dataSources;
    }
}