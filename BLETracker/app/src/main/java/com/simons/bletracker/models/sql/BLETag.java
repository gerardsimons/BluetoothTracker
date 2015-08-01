package com.simons.bletracker.models.sql;

import android.os.Parcel;
import android.os.Parcelable;

public class BLETag implements Parcelable {

    public final String name;
    public final String address;

    private Integer latestRSSI = null;

    public BLETag(String name, String address) {
        this.name = name;
        this.address = address;
        this.latestRSSI = null;
    }

    public BLETag(String name, String address, int latestRSSI) {
        this.address = address;
        this.name = name;
        this.latestRSSI = latestRSSI;
    }

    public int describeContents() {
        return 0;
    }

    // write your object's data to the passed-in Parcel
    public void writeToParcel(Parcel out, int flags) {
        out.writeString(name);
        out.writeString(address);
        out.writeInt(latestRSSI);
    }

    // this is used to regenerate your object. All Parcelables must have a CREATOR that implements these two methods
    public static final Parcelable.Creator<BLETag> CREATOR = new Parcelable.Creator<BLETag>() {
        public BLETag createFromParcel(Parcel in) {
            return new BLETag(in);
        }

        public BLETag[] newArray(int size) {
            return new BLETag[size];
        }
    };

    // example constructor that takes a Parcel and gives you an object populated with it's values
    private BLETag(Parcel in) {
        name = in.readString();
        address = in.readString();
        latestRSSI = in.readInt();
    }

    public String getAddress() {
        return address;
    }

    public boolean equals(Object o) {
        if (o instanceof BLETag) {
            BLETag other = (BLETag) o;
            if (other.getAddress().equals(getAddress())) {
                return true;
            }
        }
        return false;
    }

    public String getName() {
        return name;
    }

    public Integer getLatestRSSI() {
        return latestRSSI;
    }

    public String toString() {
        String toString = "BLETAG \nName: " + getName() + "\nAddress:" + getAddress() + "\nRSSI Value: " + latestRSSI;
        return toString;
    }

    public void setLatestRSSI(int latestRSSI) {
        this.latestRSSI = latestRSSI;
    }
}
