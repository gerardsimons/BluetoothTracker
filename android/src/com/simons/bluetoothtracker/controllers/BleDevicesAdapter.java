package com.simons.bluetoothtracker.controllers;

import java.util.ArrayList;

import android.content.Context;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.models.MyBluetoothDevice;

public class BleDevicesAdapter extends BaseAdapter {
    private final LayoutInflater inflater;

    private final ArrayList<MyBluetoothDevice> leDevices;
    private final static String TAG = "BleDevicesAdapter";

    public BleDevicesAdapter(Context context) {
        leDevices = new ArrayList<MyBluetoothDevice>();
        inflater = LayoutInflater.from(context);
    }

    public MyBluetoothDevice getExistingDevice(MyBluetoothDevice device) {
        for (MyBluetoothDevice myBTDevice : leDevices) {
            if (myBTDevice.equals(device))
                return myBTDevice;
        }
        return null;
    }

    public void addDevice(MyBluetoothDevice device) {
        MyBluetoothDevice existingDevice = getExistingDevice(device);
        if (existingDevice == null) {
            leDevices.add(device);
        } else {
            existingDevice.addRSSI(device.getLatestRSSI());
            Log.d(TAG, existingDevice.toString());
        }
        // rssiMap.put(device, rssi);
    }

    public MyBluetoothDevice getDevice(int position) {
        return leDevices.get(position);
    }

    public void clear() {
        leDevices.clear();
    }

    @Override
    public int getCount() {
        return leDevices.size();
    }

    @Override
    public Object getItem(int i) {
        return leDevices.get(i);
    }

    @Override
    public long getItemId(int i) {
        return i;
    }

    @Override
    public View getView(int i, View view, ViewGroup viewGroup) {
        ViewHolder viewHolder;
        // General ListView optimization code.
        if (view == null) {
            view = inflater.inflate(R.layout.listitem_device, null);
            viewHolder = new ViewHolder();
            viewHolder.deviceAddress = (TextView) view.findViewById(R.id.device_address);
            viewHolder.deviceName = (TextView) view.findViewById(R.id.device_name);
            viewHolder.deviceRssi = (TextView) view.findViewById(R.id.device_rssi);
            view.setTag(viewHolder);
        } else {
            viewHolder = (ViewHolder) view.getTag();
        }

        MyBluetoothDevice device = leDevices.get(i);
        final String deviceName = device.getName();
        if (deviceName != null && deviceName.length() > 0)
            viewHolder.deviceName.setText(deviceName);
        else
            viewHolder.deviceName.setText("Unknown Device");
        viewHolder.deviceAddress.setText(device.getAddress());
        viewHolder.deviceRssi.setText("" + device.getLatestRSSI() + " dBm");

        return view;
    }

    private static class ViewHolder {
        TextView deviceName;
        TextView deviceAddress;
        TextView deviceRssi;
    }
}