package com.simons.bluetoothtracker;

import java.text.DecimalFormat;
import java.util.ArrayList;

public class Fragment {

	private static final String TAG = Fragment.class.getSimpleName();
	private ArrayList<Integer> rssiValues;
	private ArrayList<Float> angles;

	private boolean active;

	private int calibrationLimit;

	private double value = Double.NaN;

	private double averageAngle = Double.NaN;

	public Fragment(int calibrationLimit) {
		this.calibrationLimit = calibrationLimit;
		rssiValues = new ArrayList<Integer>();
		angles = new ArrayList<Float>();
	}

	/**
	 * Gets the value that represents this fragment, this could be average or
	 * something else, though default is average. Future implementation could be
	 * weighted averages
	 * 
	 * @return double the value, double is used to ensure sufficient accuracy
	 *         allowing for multiple types of calculations
	 */
	public double getRepresentativeValue() {
		// if (Double.isNaN(value)) {
		// value = getAverage();
		// }
		// return value;
		double average = averageRSSI();
		DecimalFormat newFormat = new DecimalFormat("#.#");
		return Double.valueOf(newFormat.format(average));
	}

	public String toString() {
		String s = "";
		if (isCalibrated()) {
			s = "Calibrated ";
		} else
			s = "Uncalibrated ";
		s += "fragment with representative value = " + getRepresentativeValue()
				+ "\nValues=\n";
		s += Utilities.listToString(rssiValues) + "\n";
		return s;
	}

	public int valuesLeftForCalibration() {
		return calibrationLimit - rssiValues.size();
	}

	private double averageRSSI() {
		if (!rssiValues.isEmpty()) {
			int sum = 0;
			for (int i : rssiValues) {
				sum += i;
			}
			return sum / (double) rssiValues.size();
		} else
			return Double.NaN;
	}

	public void addValues(int rssi, float angle) {
		if (!isCalibrated()) {
			rssiValues.add(rssi);
			angles.add(angle);
		}
	}

	public boolean isCalibrated() {
		// Log.d(TAG, "rssiValues.size() = " + rssiValues.size());
		// Log.d(TAG, "calibrationLimit = " + calibrationLimit);
		boolean calibrated = rssiValues.size() >= calibrationLimit;
		// Log.d(TAG, "calibrated = " + calibrated);
		return calibrated;
	}

	public void setActive(boolean newActive) {
		active = newActive;
	}

	public boolean isActive() {
		return active;
	}

	public void clearData() {
		rssiValues.clear();
		angles.clear();
	}

	public double getAverageAngle() {
		double avgAngle = 0D;
		if (Double.isNaN(averageAngle)) {
			for (float angle : angles) {
				avgAngle += angle;
			}
		} else {
			return averageAngle;
		}
		return avgAngle / angles.size();
	}
}
