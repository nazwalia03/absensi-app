<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Filament\Resources\AbsensiResource\RelationManagers;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('karyawan_id')
                ->label('Karyawan')
                ->relationship('karyawan', 'nama_karyawan')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->default(now())
                ->required(),

            Forms\Components\TimePicker::make('waktu_masuk')
                ->label('Waktu Masuk')
                ->required(),

            Forms\Components\TimePicker::make('waktu_pulang')
                ->label('Waktu Pulang')
                ->required(),

            Forms\Components\FileUpload::make('foto_masuk')
                ->label('Foto Masuk')
                ->directory('absensi/foto_masuk')
                ->image(),

            Forms\Components\FileUpload::make('foto_pulang')
                ->label('Foto Pulang')
                ->directory('absensi/foto_pulang')
                ->image(),

            Forms\Components\Toggle::make('is_overtime')
                ->label('Apakah Overtime?')
                ->reactive(),

            Forms\Components\Textarea::make('alasan_overtime')
                ->label('Alasan Overtime')
                ->visible(fn ($get) => $get('is_overtime'))
                ->required(fn ($get) => $get('is_overtime')),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('karyawan.nama_karyawan')->label('Karyawan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->date(),
                Tables\Columns\TextColumn::make('waktu_masuk')->label('Masuk')->time(),
                Tables\Columns\TextColumn::make('waktu_pulang')->label('Pulang')->time(),
                Tables\Columns\IconColumn::make('is_overtime')
                    ->boolean()
                    ->label('Lembur'),
                Tables\Columns\TextColumn::make('alasan_overtime')
                    ->label('Alasan Overtime')
                    ->toggleable()
                    ->limit(30),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensis::route('/'),
            // 'create' => Pages\CreateAbsensi::route('/create'),
            // 'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
{
    return auth()->user()->hasRole('admin');
}
}
