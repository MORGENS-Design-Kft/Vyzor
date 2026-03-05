"use client";

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { CLARITY_DIMENSIONS } from "@/lib/providers/clarity/types";

interface DimensionPickerProps {
  label: string;
  value: string;
  onChange: (value: string) => void;
}

export function DimensionPicker({
  label,
  value,
  onChange,
}: DimensionPickerProps) {
  return (
    <div className="space-y-1.5">
      <label className="text-sm font-medium">{label}</label>
      <Select value={value} onValueChange={onChange}>
        <SelectTrigger className="w-[200px]">
          <SelectValue placeholder="None" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="none">None</SelectItem>
          {CLARITY_DIMENSIONS.map((dim) => (
            <SelectItem key={dim} value={dim}>
              {dim}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}
