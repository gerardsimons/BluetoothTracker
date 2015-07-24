package com.simons.bletracker;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.simons.bletracker.models.BLETag;
import com.simons.bletracker.views.CircularValueIndicator;

import java.util.ArrayList;

public class BleDevicesAdapter extends BaseAdapter {
    private final LayoutInflater inflater;

    private final ArrayList<BLETag> leDevices;
    private final static String TAG = "BleDevicesAdapter";

    public BleDevicesAdapter(Context context) {
        leDevices = new ArrayList<BLETag>();
        inflater = LayoutInflater.from(context);
    }

    public BLETag getExistingDevice(String address) {
        for (BLETag myBTDevice : leDevices) {
            if (myBTDevice.getAddress().equals(address))
                return myBTDevice;
        }
        return null;
    }

    public void addTag(String name, String address, int rssi) {
        BLETag existingDevice = getExistingDevice(address);
        if (existingDevice == null) {
            leDevices.add(new BLETag(name,address,rssi));
        } else {
            existingDevice.setLatestRSSI(rssi);
        }
    }

    public void addTag(BLETag newTag) {
        BLETag existingDevice = getExistingDevice(newTag.getAddress());
        if (existingDevice == null) {
            leDevices.add(newTag);
        } else {
            existingDevice.setLatestRSSI(newTag.getLatestRSSI());
        }
    }

    public BLETag getTag(int position) {
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
            viewHolder.deviceRssi = (CircularValueIndicator) view.findViewById(R.id.device_rssi);

            view.setTag(viewHolder);
        } else {
            viewHolder = (ViewHolder) view.getTag();
        }

        BLETag device = leDevices.get(i);
        final String deviceName = device.getName();
        if (deviceName != null && deviceName.length() > 0)
            viewHolder.deviceName.setText(device.getName());
        else
            viewHolder.deviceName.setText("Unknown Device");

        viewHolder.deviceAddress.setText(device.getAddress());

        //-20 is considered best
        //-100 considered worst


        Integer latestRSSI = device.getLatestRSSI();
        viewHolder.deviceRssi.setStrengthValue(BLETrackerApplication.RelativeSignalStrength(latestRSSI));
         viewHolder.deviceRssi.setRawValue(latestRSSI);

        return view;
    }

    private static class ViewHolder {
        TextView deviceName;
        TextView deviceAddress;
        CircularValueIndicator deviceRssi;
    }
}
