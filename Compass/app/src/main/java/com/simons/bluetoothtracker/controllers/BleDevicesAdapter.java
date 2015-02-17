package com.simons.bluetoothtracker.controllers;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.models.MyBluetoothDevice;
import com.simons.bluetoothtracker.models.ProductType;
import com.simons.bluetoothtracker.views.DeviceStrengthIndicator;

import java.util.ArrayList;

public class BleDevicesAdapter extends BaseAdapter {
    private final LayoutInflater inflater;

    private final ArrayList<MyBluetoothDevice> leDevices;
    private final static String TAG = "BleDevicesAdapter";

    public BleDevicesAdapter(Context context) {
        leDevices = new ArrayList<MyBluetoothDevice>();
        inflater = LayoutInflater.from(context);
    }

    public MyBluetoothDevice getExistingDevice(String address) {
        for (MyBluetoothDevice myBTDevice : leDevices) {
            if (myBTDevice.getAddress().equals(address))
                return myBTDevice;
        }
        return null;
    }

    public void addDevice(String name, String address, int rssi, ProductType productType) {
        MyBluetoothDevice existingDevice = getExistingDevice(address);
        if (existingDevice == null) {
            leDevices.add(new MyBluetoothDevice(name,address,rssi,productType));
        } else {
            existingDevice.setLatestRSSI(rssi);
        }
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
            viewHolder.deviceRssi = (DeviceStrengthIndicator) view.findViewById(R.id.device_rssi);

            view.setTag(viewHolder);
        } else {
            viewHolder = (ViewHolder) view.getTag();
        }

        MyBluetoothDevice device = leDevices.get(i);
        final String deviceName = device.getName();
        if (deviceName != null && deviceName.length() > 0)
            viewHolder.deviceName.setText(device.getName());
        else
            viewHolder.deviceName.setText("Unknown Device");

        viewHolder.deviceAddress.setText(device.getAddress());

        //Get the drawable belonging to the product type
        int drawableId = BluetoothTrackerApplication.IdForProductType(device.getProductType());
        if(drawableId != -1)
            viewHolder.deviceRssi.setImage(drawableId);


        Integer latestRSSI = device.getLatestRSSI();
        float strengthValue = 0F;
        if(latestRSSI != null)
            strengthValue = Compass.getRatioStrength(latestRSSI);

        viewHolder.deviceRssi.setStrengthValue(strengthValue);

        return view;
    }

    private static class ViewHolder {
        TextView deviceName;
        TextView deviceAddress;
        DeviceStrengthIndicator deviceRssi;
    }
}
