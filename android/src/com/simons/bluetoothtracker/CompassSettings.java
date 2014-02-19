package com.simons.bluetoothtracker;

/**
 * Created by gerardsimons on 19/02/14.
 */
public class CompassSettings {

    public boolean showDebugText;
    public boolean showPointer;
    public boolean showColors;

    public int nrOfFragments;
    public int calibrationLimit;
    public int maxValuesPerFragment;
    public int pointerWidth;

    public CompassSettings(boolean showColors, boolean showPointer, boolean showDebugText, int nrOfFragments, int calibrationLimit, int maxValuesPerFragment, int pointerWidth) {
        this.showColors = showColors;
        this.showPointer = showPointer;
        this.showDebugText = showDebugText;

        this.nrOfFragments = nrOfFragments;
        this.calibrationLimit = calibrationLimit;
        this.maxValuesPerFragment = maxValuesPerFragment;
        this.pointerWidth = pointerWidth;
    }

    @Override
    public boolean equals(Object o) {
        if(o instanceof CompassSettings) {
            CompassSettings otherSettings = (CompassSettings) o;
            return showColors == otherSettings.showColors && showPointer == otherSettings.showPointer && showDebugText == otherSettings.showDebugText && nrOfFragments == otherSettings.nrOfFragments && otherSettings.calibrationLimit == otherSettings.calibrationLimit && maxValuesPerFragment == otherSettings.maxValuesPerFragment && pointerWidth == otherSettings.pointerWidth;
        }
        else return false;
    }
}
