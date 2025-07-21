<?php


namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model; // <-- Asegúrate de importar la clase Model

use Illuminate\Validation\Rules\Unique;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id') // Input de empresa id con estado oculto Hidden
                    ->default(auth()->user()->business_id),

                Forms\Components\TextInput::make('name')
                    ->label('Nombre de usuario')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('business_id', auth()->user()->business_id)

                    )
                    ->validationMessages([
                        'unique' => 'Este correo electrónico ya está registrado en este negocio.',
                    ]),
                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    // Desactivamos la visibilidad condicional para que funcione en el modal
                    // ->visibleOn('create') 
                    ->dehydrated(fn ($state) => filled($state)) // Solo se guarda si se llena
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                Forms\Components\Select::make('roles')
                
                    ->label('Asignar role')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        // Esta closure filtra la consulta para no mostrar el rol de Super Admin
                        modifyQueryUsing: fn (Builder $query) => $query->where('name', '!=', 'super-admin')
                    )
                    ->preload()
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->label('Roles')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creacion')
                    ->dateTime('d-M-Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'), // Correctamente comentado para usar modal
            //'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
    
    /**
     * Esta función se ejecuta justo antes de que se cree un nuevo registro.
     * Es el lugar perfecto para inyectar el business_id cuando se usa un modal.
     */
    public static function creating(Model $model): void
    {
        $model->business_id = auth()->user()->business_id;
    }
}
